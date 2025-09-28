#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import json
import re
import string
from collections import Counter
from typing import List, Dict, Optional, Tuple
import argparse

try:
    import nltk
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.metrics.pairwise import cosine_similarity
    import numpy as np
    from langdetect import detect, DetectorFactory
except ImportError as e:
    print(json.dumps({"error": f"Missing dependency: {e}. Run: pip install -r requirements.txt"}))
    sys.exit(1)

# Optional advanced dependencies
try:
    from sentence_transformers import SentenceTransformer
    SENTENCE_TRANSFORMERS_AVAILABLE = True
except ImportError:
    SENTENCE_TRANSFORMERS_AVAILABLE = False

# Set seed for consistent language detection
DetectorFactory.seed = 0


class MultilingualChatAnalyzer:
    def __init__(self):
        self._download_nltk_data()
        
        # Initialize multilingual sentence transformer if available
        self.sentence_model = None
        if SENTENCE_TRANSFORMERS_AVAILABLE:
            try:
                self.sentence_model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
            except Exception:
                # Fallback to basic analysis if model fails to load
                self.sentence_model = None
        
        # Supported languages (top 10 + Ukrainian + Belarusian)
        self.supported_languages = {
            'en': 'English',
            'zh': 'Chinese', 
            'hi': 'Hindi',
            'es': 'Spanish',
            'fr': 'French',
            'ar': 'Arabic',
            'bn': 'Bengali',
            'pt': 'Portuguese',
            'ru': 'Russian',
            'ja': 'Japanese',
            'uk': 'Ukrainian',
            'be': 'Belarusian'
        }
        
        # Multilingual category dictionaries
        self.categories = {
            'crypto': {
                'en': [
                    'bitcoin', 'btc', 'ethereum', 'eth', 'cryptocurrency', 'crypto', 'blockchain',
                    'mining', 'wallet', 'exchange', 'defi', 'nft', 'token', 'coin', 'trading',
                    'binance', 'coinbase', 'metamask', 'usdt', 'usdc', 'dogecoin', 'litecoin',
                    'altcoin', 'hodl', 'moon', 'satoshi', 'dex', 'swap', 'yield', 'farming',
                    'staking', 'web3', 'dao', 'ico', 'ido', 'airdrop', 'mint', 'burn',
                    'notcoin', 'pixel', 'hamster', 'pocketfi', 'doge', 'shiba'
                ],
                'ru': [
                    'криптовалюта', 'биткоин', 'эфириум', 'блокчейн', 'майнинг', 'кошелек',
                    'биржа', 'торговля', 'токен', 'монета', 'альткоин', 'децентрализация',
                    'вложился', 'инвестиции', 'курс', 'цена', 'стоимость', 'рост', 'падение',
                    'волатильность', 'крипта', 'памп', 'дамп', 'луна', 'ламбо', 'шиба'
                ],
                'es': [
                    'bitcoin', 'criptomoneda', 'cripto', 'cadena de bloques', 'minería', 'billetera',
                    'intercambio', 'comercio', 'token', 'moneda', 'altcoin', 'descentralización'
                ],
                'fr': [
                    'bitcoin', 'cryptomonnaie', 'crypto', 'blockchain', 'minage', 'portefeuille',
                    'échange', 'commerce', 'jeton', 'pièce', 'altcoin', 'décentralisation'
                ],
                'de': [
                    'bitcoin', 'kryptowährung', 'krypto', 'blockchain', 'bergbau', 'geldbörse',
                    'austausch', 'handel', 'token', 'münze', 'altcoin', 'dezentralisierung'
                ],
                'zh': [
                    '比特币', '加密货币', '区块链', '挖矿', '钱包', '交易所', '交易', '代币', '硬币', '山寨币'
                ],
                'ja': [
                    'ビットコイン', '暗号通貨', 'ブロックチェーン', 'マイニング', 'ウォレット', '取引所', '取引', 'トークン', 'コイン', 'アルトコイン'
                ],
                'uk': [
                    'криптовалюта', 'біткоїн', 'ефіріум', 'блокчейн', 'майнінг', 'гаманець',
                    'біржа', 'торгівля', 'токен', 'монета', 'альткоїн', 'децентралізація'
                ],
                'be': [
                    'крыптавалюта', 'біткоін', 'эфіры', 'блокчэйн', 'майнінг', 'кашалёк',
                    'біржа', 'гандаль', 'токен', 'манета', 'альткоін'
                ]
            },
            
            'business': {
                'en': [
                    'business', 'company', 'startup', 'entrepreneur', 'partnership', 'deal',
                    'contract', 'agreement', 'meeting', 'conference', 'presentation', 'pitch',
                    'investor', 'funding', 'revenue', 'profit', 'sales', 'client', 'customer',
                    'marketing', 'advertising', 'promotion', 'campaign', 'strategy'
                ],
                'ru': [
                    'бизнес', 'компания', 'стартап', 'предприниматель', 'партнерство', 'сделка',
                    'контракт', 'соглашение', 'встреча', 'конференция', 'презентация', 'инвестор',
                    'финансирование', 'выручка', 'прибыль', 'продажи', 'клиент', 'заказчик',
                    'маркетинг', 'реклама', 'продвижение', 'кампания', 'стратегия', 'проект'
                ],
                'es': [
                    'negocio', 'empresa', 'startup', 'emprendedor', 'asociación', 'trato',
                    'contrato', 'acuerdo', 'reunión', 'conferencia', 'presentación', 'inversor',
                    'financiación', 'ingresos', 'beneficio', 'ventas', 'cliente', 'marketing'
                ],
                'fr': [
                    'affaires', 'entreprise', 'startup', 'entrepreneur', 'partenariat', 'accord',
                    'contrat', 'réunion', 'conférence', 'présentation', 'investisseur',
                    'financement', 'revenus', 'profit', 'ventes', 'client', 'marketing'
                ],
                'de': [
                    'geschäft', 'unternehmen', 'startup', 'unternehmer', 'partnerschaft', 'deal',
                    'vertrag', 'vereinbarung', 'treffen', 'konferenz', 'präsentation', 'investor',
                    'finanzierung', 'umsatz', 'gewinn', 'verkäufe', 'kunde', 'marketing'
                ],
                'zh': [
                    '商业', '公司', '创业', '企业家', '合作', '交易', '合同', '协议', '会议', '会议',
                    '演示', '投资者', '资金', '收入', '利润', '销售', '客户', '营销'
                ],
                'ja': [
                    'ビジネス', '会社', 'スタートアップ', '起業家', 'パートナーシップ', '取引',
                    '契約', '合意', '会議', 'カンファレンス', 'プレゼンテーション', '投資家'
                ],
                'uk': [
                    'бізнес', 'компанія', 'стартап', 'підприємець', 'партнерство', 'угода',
                    'контракт', 'домовленість', 'зустріч', 'конференція', 'презентація', 'інвестор'
                ],
                'be': [
                    'бізнес', 'кампанія', 'стартап', 'прадпрымальнік', 'партнёрства', 'здзелка',
                    'кантракт', 'дамоўленасць', 'сустрэча', 'канферэнцыя', 'прэзентацыя'
                ]
            },
            
            'banking': {
                'en': [
                    'bank', 'banking', 'finance', 'financial', 'loan', 'credit', 'mortgage',
                    'investment', 'insurance', 'payment', 'transfer', 'account', 'card',
                    'visa', 'mastercard', 'paypal', 'swift', 'iban', 'deposit', 'withdrawal'
                ],
                'ru': [
                    'банк', 'банковский', 'финансы', 'кредит', 'займ', 'ипотека', 'инвестиции',
                    'страхование', 'платеж', 'перевод', 'счет', 'карта', 'депозит', 'монобанк',
                    'приватбанк', 'ощадбанк', 'райффайзен', 'альфа-банк', 'комиссия', 'ставка'
                ],
                'es': [
                    'banco', 'bancario', 'finanzas', 'financiero', 'préstamo', 'crédito', 'hipoteca',
                    'inversión', 'seguro', 'pago', 'transferencia', 'cuenta', 'tarjeta'
                ],
                'fr': [
                    'banque', 'bancaire', 'finance', 'financier', 'prêt', 'crédit', 'hypothèque',
                    'investissement', 'assurance', 'paiement', 'virement', 'compte', 'carte'
                ],
                'de': [
                    'bank', 'banking', 'finanzen', 'finanziell', 'darlehen', 'kredit', 'hypothek',
                    'investition', 'versicherung', 'zahlung', 'überweisung', 'konto', 'karte'
                ],
                'zh': [
                    '银行', '银行业', '金融', '贷款', '信贷', '抵押', '投资', '保险', '支付', '转账', '账户', '卡'
                ],
                'ja': [
                    '銀行', 'バンキング', '金融', 'ローン', 'クレジット', '住宅ローン', '投資', '保険', '支払い', '振込', '口座', 'カード'
                ],
                'uk': [
                    'банк', 'банківський', 'фінанси', 'кредит', 'позика', 'іпотека', 'інвестиції',
                    'страхування', 'платіж', 'переказ', 'рахунок', 'картка', 'депозит'
                ],
                'be': [
                    'банк', 'банкаўскі', 'фінансы', 'крэдыт', 'пазыка', 'іпатэка', 'інвестыцыі',
                    'страхаванне', 'плацёж', 'перавод', 'рахунак', 'картка'
                ]
            },
            
            'technology': {
                'en': [
                    'technology', 'tech', 'software', 'development', 'programming', 'coding',
                    'app', 'application', 'website', 'web', 'mobile', 'ai', 'artificial intelligence',
                    'machine learning', 'data', 'database', 'api', 'cloud', 'server', 'hosting'
                ],
                'ru': [
                    'технологии', 'разработка', 'программирование', 'приложение', 'сайт',
                    'мобильный', 'искусственный интеллект', 'данные', 'база данных', 'сервер',
                    'хостинг', 'облако', 'софт', 'код', 'кодинг', 'веб'
                ],
                'es': [
                    'tecnología', 'desarrollo', 'programación', 'aplicación', 'sitio web',
                    'móvil', 'inteligencia artificial', 'datos', 'base de datos', 'servidor'
                ],
                'fr': [
                    'technologie', 'développement', 'programmation', 'application', 'site web',
                    'mobile', 'intelligence artificielle', 'données', 'base de données', 'serveur'
                ],
                'de': [
                    'technologie', 'entwicklung', 'programmierung', 'anwendung', 'webseite',
                    'mobil', 'künstliche intelligenz', 'daten', 'datenbank', 'server'
                ],
                'zh': [
                    '技术', '开发', '编程', '应用', '网站', '移动', '人工智能', '数据', '数据库', '服务器'
                ],
                'ja': [
                    'テクノロジー', '開発', 'プログラミング', 'アプリケーション', 'ウェブサイト',
                    'モバイル', '人工知能', 'データ', 'データベース', 'サーバー'
                ],
                'uk': [
                    'технології', 'розробка', 'програмування', 'додаток', 'сайт',
                    'мобільний', 'штучний інтелект', 'дані', 'база даних', 'сервер'
                ],
                'be': [
                    'тэхналогіі', 'распрацоўка', 'праграмаванне', 'дадатак', 'сайт',
                    'мабільны', 'штучны інтэлект', 'дадзеныя', 'база дадзеных', 'сервер'
                ]
            },
            
            'gaming': {
                'en': [
                    'game', 'gaming', 'gamer', 'play', 'player', 'level', 'quest', 'achievement',
                    'console', 'steam', 'playstation', 'xbox', 'nintendo', 'esports', 'streamer',
                    'twitch', 'tournament', 'clan', 'guild', 'mmorpg', 'fps', 'moba', 'rpg'
                ],
                'ru': [
                    'игра', 'игры', 'играть', 'геймер', 'уровень', 'квест', 'достижение',
                    'консоль', 'стим', 'плейстейшн', 'иксбокс', 'нинтендо', 'геймплей',
                    'игрушка', 'поиграем', 'зависаю', 'затягивает', 'турнир', 'клан', 'гильдия'
                ],
                'es': [
                    'juego', 'jugador', 'jugar', 'nivel', 'misión', 'logro', 'consola',
                    'torneo', 'clan', 'gremio', 'gameplay'
                ],
                'fr': [
                    'jeu', 'joueur', 'jouer', 'niveau', 'quête', 'succès', 'console',
                    'tournoi', 'clan', 'guilde', 'gameplay'
                ],
                'de': [
                    'spiel', 'spieler', 'spielen', 'level', 'quest', 'erfolg', 'konsole',
                    'turnier', 'clan', 'gilde', 'gameplay'
                ],
                'zh': [
                    '游戏', '玩家', '玩', '等级', '任务', '成就', '游戏机', '锦标赛', '公会'
                ],
                'ja': [
                    'ゲーム', 'ゲーマー', 'プレイ', 'レベル', 'クエスト', '実績', 'コンソール', 'トーナメント', 'クラン', 'ギルド'
                ],
                'uk': [
                    'гра', 'гравець', 'грати', 'рівень', 'квест', 'досягнення', 'консоль',
                    'турнір', 'клан', 'гільдія', 'геймплей'
                ],
                'be': [
                    'гульня', 'гулец', 'гуляць', 'узровень', 'квэст', 'дасягненне', 'кансоль',
                    'турнір', 'клан', 'гільдыя'
                ]
            },
            
            'social': {
                'en': [
                    'friend', 'buddy', 'family', 'brother', 'sister', 'mom', 'dad', 'mother',
                    'father', 'uncle', 'aunt', 'cousin', 'girlfriend', 'boyfriend', 'wife',
                    'husband', 'partner', 'grandmother', 'grandfather', 'grandma', 'grandpa'
                ],
                'ru': [
                    'друг', 'подруга', 'семья', 'брат', 'сестра', 'мама', 'папа', 'мать',
                    'отец', 'дядя', 'тетя', 'двоюродный', 'жена', 'муж', 'партнер',
                    'бабушка', 'дедушка', 'родственники', 'родители', 'сын', 'дочь'
                ],
                'es': [
                    'amigo', 'familia', 'hermano', 'hermana', 'mamá', 'papá', 'madre',
                    'padre', 'tío', 'tía', 'primo', 'novia', 'novio', 'esposa', 'esposo'
                ],
                'fr': [
                    'ami', 'famille', 'frère', 'sœur', 'maman', 'papa', 'mère',
                    'père', 'oncle', 'tante', 'cousin', 'petite amie', 'petit ami', 'épouse', 'époux'
                ],
                'de': [
                    'freund', 'familie', 'bruder', 'schwester', 'mama', 'papa', 'mutter',
                    'vater', 'onkel', 'tante', 'cousin', 'freundin', 'freund', 'ehefrau', 'ehemann'
                ],
                'zh': [
                    '朋友', '家庭', '兄弟', '姐妹', '妈妈', '爸爸', '母亲', '父亲', '叔叔', '阿姨', '表兄弟', '女朋友', '男朋友', '妻子', '丈夫'
                ],
                'ja': [
                    '友達', '家族', '兄弟', '姉妹', 'お母さん', 'お父さん', '母', '父', 'おじさん', 'おばさん', 'いとこ', '彼女', '彼氏', '妻', '夫'
                ],
                'uk': [
                    'друг', 'подруга', 'сім\'я', 'брат', 'сестра', 'мама', 'тато', 'мати',
                    'батько', 'дядько', 'тітка', 'двоюрідний', 'дружина', 'чоловік', 'партнер'
                ],
                'be': [
                    'сябар', 'сяброўка', 'сям\'я', 'брат', 'сястра', 'мама', 'тата', 'маці',
                    'бацька', 'дзядзька', 'цётка', 'двюродны', 'жонка', 'муж'
                ]
            }
        }
        
        self._build_category_vectors()

    def _download_nltk_data(self):
        """Download required NLTK data"""
        try:
            nltk.data.find('tokenizers/punkt')
            nltk.data.find('corpora/stopwords')
        except LookupError:
            nltk.download('punkt', quiet=True)
            nltk.download('stopwords', quiet=True)

    def _build_category_vectors(self):
        """Build TF-IDF vectors for all categories across all languages"""
        self.vectorizer = TfidfVectorizer(
            lowercase=True,
            stop_words=None,
            ngram_range=(1, 2),
            max_features=2000,
            min_df=1,
            max_df=0.95
        )
        
        category_docs = []
        self.category_names = []
        
        for category, languages in self.categories.items():
            # Combine all languages for this category
            all_keywords = []
            for lang_keywords in languages.values():
                all_keywords.extend(lang_keywords)
            
            doc = ' '.join(all_keywords)
            category_docs.append(doc)
            self.category_names.append(category)
        
        self.category_vectors = self.vectorizer.fit_transform(category_docs)

    def detect_language(self, text: str) -> str:
        """Detect the language of the text"""
        if not text or len(text.strip()) < 5:
            return 'en'  # Default to English for very short texts
        
        try:
            # Try multiple times for better accuracy with short texts
            detected = detect(text)
            
            # For very short texts, try to detect by character patterns
            if len(text.strip()) < 15:
                # Check for Cyrillic characters (Russian, Ukrainian, Belarusian)
                cyrillic_chars = sum(1 for c in text if '\u0400' <= c <= '\u04FF')
                if cyrillic_chars > 0:
                    # Try to distinguish between Russian, Ukrainian, Belarusian
                    if any(char in text for char in ['і', 'ї', 'є']):  # Ukrainian specific
                        return 'uk'
                    elif any(char in text for char in ['ў', 'ё']):  # Belarusian specific
                        return 'be'
                    else:
                        return 'ru'  # Default to Russian for Cyrillic
                
                # Check for Chinese characters
                chinese_chars = sum(1 for c in text if '\u4e00' <= c <= '\u9fff')
                if chinese_chars > 0:
                    return 'zh'
                
                # Check for Japanese characters
                japanese_chars = sum(1 for c in text if '\u3040' <= c <= '\u309f' or '\u30a0' <= c <= '\u30ff')
                if japanese_chars > 0:
                    return 'ja'
            
            # Map some common variations
            if detected in self.supported_languages:
                return detected
            elif detected in ['ca', 'gl']:  # Catalan, Galician -> Spanish
                return 'es'
            elif detected in ['it']:  # Italian -> closest supported
                return 'es'
            elif detected in ['nl', 'da', 'sv', 'no']:  # Germanic languages -> German
                return 'de'
            elif detected in ['ko']:  # Korean -> Japanese (similar script)
                return 'ja'
            elif detected in ['mk', 'bg', 'sr']:  # Macedonian, Bulgarian, Serbian -> Russian (Cyrillic)
                return 'ru'
            else:
                return 'en'  # Default fallback
        except Exception:
            return 'en'  # Default fallback

    def _clean_text(self, text: str) -> str:
        """Clean and normalize text"""
        if not text:
            return ""
        
        text = text.lower()
        # Remove URLs
        text = re.sub(r'http[s]?://(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\\(\\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+', '', text)
        # Remove emails
        text = re.sub(r'\S+@\S+', '', text)
        # Remove phone numbers
        text = re.sub(r'[\+]?[1-9]?[0-9]{7,15}', '', text)
        # Keep only letters, numbers, and spaces
        text = re.sub(r'[^\w\s]', ' ', text)
        # Normalize whitespace
        text = re.sub(r'\s+', ' ', text)
        
        return text.strip()

    def _extract_text_from_messages(self, messages: List[Dict]) -> Tuple[str, str]:
        """Extract and combine text from all messages, return (text, detected_language)"""
        texts = []
        raw_texts = []  # Keep original text for language detection
        
        for msg in messages:
            # Try different possible text fields
            text = ""
            for field in ['text', 'body', 'content', 'text_content', 'message']:
                if field in msg and msg[field]:
                    text = str(msg[field])
                    break
            
            if text:
                raw_texts.append(text)  # Keep original for language detection
                cleaned = self._clean_text(text)
                if len(cleaned) > 3:  # Only include meaningful text
                    texts.append(cleaned)
        
        combined_text = ' '.join(texts)
        raw_combined = ' '.join(raw_texts)
        detected_language = self.detect_language(raw_combined)  # Use raw text for detection
        
        return combined_text, detected_language

    def analyze_chat(self, messages: List[Dict]) -> Optional[str]:
        """Analyze chat messages and return the best category"""
        if not messages:
            return None
        
        text, detected_lang = self._extract_text_from_messages(messages)
        
        if not text or len(text.split()) < 3:
            return None
        
        try:
            # Method 1: Use sentence transformers if available
            if self.sentence_model:
                category = self._analyze_with_sentence_transformer(text, detected_lang)
                if category:
                    return category
            
            # Method 2: Fallback to TF-IDF analysis
            return self._analyze_with_tfidf(text, detected_lang)
            
        except Exception as e:
            return None

    def _analyze_with_sentence_transformer(self, text: str, detected_lang: str) -> Optional[str]:
        """Analyze using sentence transformers (multilingual)"""
        try:
            text_embedding = self.sentence_model.encode([text])
            
            best_score = 0
            best_category = None
            
            for category, languages in self.categories.items():
                # Get keywords for detected language or fallback to English
                keywords = languages.get(detected_lang, languages.get('en', []))
                
                if not keywords:
                    continue
                
                # Create category text from keywords
                category_text = ' '.join(keywords[:20])  # Limit to avoid too long texts
                category_embedding = self.sentence_model.encode([category_text])
                
                # Calculate similarity
                similarity = cosine_similarity(text_embedding, category_embedding)[0][0]
                
                if similarity > best_score:
                    best_score = similarity
                    best_category = category
            
            # Return category if confidence is high enough
            if best_score > 0.15:  # Threshold for sentence transformer
                return best_category
            
            return None
            
        except Exception:
            return None

    def _analyze_with_tfidf(self, text: str, detected_lang: str) -> Optional[str]:
        """Analyze using TF-IDF (fallback method)"""
        try:
            text_vector = self.vectorizer.transform([text])
            similarities = cosine_similarity(text_vector, self.category_vectors)[0]
            
            best_idx = np.argmax(similarities)
            best_score = similarities[best_idx]
            
            # Lower threshold for TF-IDF
            if best_score < 0.05:
                return None
            
            return self.category_names[best_idx]
            
        except Exception:
            return None

    def get_analysis_details(self, messages: List[Dict]) -> Dict:
        """Get detailed analysis information"""
        if not messages:
            return {"error": "No messages provided"}
        
        text, detected_lang = self._extract_text_from_messages(messages)
        
        if not text:
            return {"error": "No text extracted from messages"}
        
        try:
            results = {
                "extracted_text": text[:200] + "..." if len(text) > 200 else text,
                "text_length": len(text),
                "word_count": len(text.split()),
                "message_count": len(messages),
                "detected_language": detected_lang,
                "language_name": self.supported_languages.get(detected_lang, "Unknown"),
                "category_scores": {},
                "analysis_method": "unknown"
            }
            
            # Try sentence transformer first
            if self.sentence_model:
                try:
                    text_embedding = self.sentence_model.encode([text])
                    results["analysis_method"] = "sentence_transformer"
                    
                    for category, languages in self.categories.items():
                        keywords = languages.get(detected_lang, languages.get('en', []))
                        if keywords:
                            category_text = ' '.join(keywords[:20])
                            category_embedding = self.sentence_model.encode([category_text])
                            similarity = cosine_similarity(text_embedding, category_embedding)[0][0]
                            results["category_scores"][category] = float(similarity)
                    
                except Exception:
                    # Fallback to TF-IDF
                    pass
            
            # Fallback to TF-IDF if sentence transformer failed
            if not results["category_scores"]:
                text_vector = self.vectorizer.transform([text])
                similarities = cosine_similarity(text_vector, self.category_vectors)[0]
                results["analysis_method"] = "tfidf"
                
                for i, category in enumerate(self.category_names):
                    results["category_scores"][category] = float(similarities[i])
            
            # Sort and determine best category
            sorted_scores = sorted(results["category_scores"].items(), key=lambda x: x[1], reverse=True)
            results["ranked_categories"] = sorted_scores
            
            threshold = 0.15 if results["analysis_method"] == "sentence_transformer" else 0.05
            best_category = sorted_scores[0][0] if sorted_scores and sorted_scores[0][1] >= threshold else None
            results["best_category"] = best_category
            results["confidence"] = float(sorted_scores[0][1]) if sorted_scores else 0.0
            
            return results
            
        except Exception as e:
            return {"error": str(e)}


def main():
    parser = argparse.ArgumentParser(description='Multilingual chat message analyzer for semantic tagging')
    parser.add_argument('--messages', type=str, help='JSON string of messages')
    parser.add_argument('--file', type=str, help='JSON file containing messages')
    parser.add_argument('--debug', action='store_true', help='Return detailed analysis')
    
    args = parser.parse_args()
    
    # Initialize analyzer
    analyzer = MultilingualChatAnalyzer()
    
    # Get messages from input
    messages = []
    
    if args.file:
        try:
            with open(args.file, 'r', encoding='utf-8') as f:
                data = json.load(f)
                messages = data if isinstance(data, list) else data.get('messages', [])
        except Exception as e:
            print(json.dumps({"error": f"Failed to read file: {e}"}))
            return
    
    elif args.messages:
        try:
            data = json.loads(args.messages)
            messages = data if isinstance(data, list) else data.get('messages', [])
        except Exception as e:
            print(json.dumps({"error": f"Failed to parse JSON: {e}"}))
            return
    
    else:
        # Read from stdin
        try:
            data = json.load(sys.stdin)
            messages = data if isinstance(data, list) else data.get('messages', [])
        except Exception as e:
            print(json.dumps({"error": f"Failed to read from stdin: {e}"}))
            return
    
    # Perform analysis
    if args.debug:
        result = analyzer.get_analysis_details(messages)
    else:
        category = analyzer.analyze_chat(messages)
        result = {"category": category}
    
    # Output result as JSON
    print(json.dumps(result, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
