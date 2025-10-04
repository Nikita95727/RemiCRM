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
except ImportError as e:
    print(json.dumps({"error": f"Missing dependency: {e}. Run: pip install -r requirements.txt"}))
    sys.exit(1)


class ChatAnalyzer:
    def __init__(self):
        self._download_nltk_data()

        self.categories = {
            'crypto': [
                # English terms
                'bitcoin', 'btc', 'ethereum', 'eth', 'cryptocurrency', 'crypto', 'blockchain',
                'mining', 'wallet', 'exchange', 'defi', 'nft', 'token', 'coin', 'trading',
                'binance', 'coinbase', 'metamask', 'usdt', 'usdc', 'dogecoin', 'litecoin',
                'altcoin', 'hodl', 'moon', 'satoshi', 'dex', 'swap', 'yield', 'farming',
                'staking', 'web3', 'dao', 'ico', 'ido', 'airdrop', 'mint', 'burn',
                'notcoin', 'pixel', 'hamster', 'pocketfi',

                # Russian terms
                'криптовалюта', 'биткоин', 'эфириум', 'блокчейн', 'майнинг', 'кошелек',
                'биржа', 'торговля', 'токен', 'монета', 'альткоин', 'децентрализация',
                'вложился', 'инвестиции', 'курс', 'цена', 'стоимость', 'рост', 'падение',
                'волатильность', 'крипта', 'памп', 'дамп', 'луна', 'ламбо'
            ],

            'banking': [
                # English terms
                'bank', 'banking', 'finance', 'financial', 'loan', 'credit', 'mortgage',
                'investment', 'insurance', 'payment', 'transfer', 'account', 'card',
                'visa', 'mastercard', 'paypal', 'swift', 'iban', 'deposit', 'withdrawal',

                # Russian terms
                'банк', 'банковский', 'финансы', 'кредит', 'займ', 'ипотека', 'инвестиции',
                'страхование', 'платеж', 'перевод', 'счет', 'карта', 'депозит', 'монобанк',
                'приватбанк', 'ощадбанк', 'райффайзен', 'альфа-банк', 'monobank',
                'privatbank', 'комиссия', 'ставка', 'услуги'
            ],

            'business': [
                # English terms
                'business', 'company', 'startup', 'entrepreneur', 'partnership', 'deal',
                'contract', 'agreement', 'meeting', 'conference', 'presentation', 'pitch',
                'investor', 'funding', 'revenue', 'profit', 'sales', 'client', 'customer',
                'marketing', 'advertising', 'promotion', 'campaign', 'strategy',

                # Russian terms
                'бизнес', 'компания', 'стартап', 'предприниматель', 'партнерство', 'сделка',
                'контракт', 'соглашение', 'встреча', 'конференция', 'презентация', 'инвестор',
                'финансирование', 'выручка', 'прибыль', 'продажи', 'клиент', 'заказчик',
                'маркетинг', 'реклама', 'продвижение', 'кампания', 'стратегия', 'проект',
                'коммерческое', 'предложение', 'бюджет'
            ],

            'social': [
                # English terms
                'friend', 'buddy', 'family', 'brother', 'sister', 'mom', 'dad', 'mother',
                'father', 'uncle', 'aunt', 'cousin', 'girlfriend', 'boyfriend', 'wife',
                'husband', 'partner', 'grandmother', 'grandfather', 'grandma', 'grandpa',

                # Russian terms
                'друг', 'подруга', 'семья', 'брат', 'сестра', 'мама', 'папа', 'мать',
                'отец', 'дядя', 'тетя', 'двоюродный', 'жена', 'муж', 'партнер',
                'бабушка', 'дедушка', 'родственники', 'родители', 'сын', 'дочь',
                'внук', 'внучка', 'племянник', 'племянница', 'гости', 'виделись'
            ],

            'gaming': [
                # English terms
                'game', 'gaming', 'gamer', 'play', 'player', 'level', 'quest', 'achievement',
                'console', 'steam', 'playstation', 'xbox', 'nintendo', 'esports', 'streamer',
                'twitch', 'tournament', 'clan', 'guild', 'mmorpg', 'fps', 'moba', 'rpg',

                # Russian terms
                'игра', 'игры', 'играть', 'геймер', 'уровень', 'квест', 'достижение',
                'консоль', 'стим', 'плейстейшн', 'иксбокс', 'нинтендо', 'геймплей',
                'игрушка', 'поиграем', 'зависаю', 'затягивает', 'турнир', 'клан',
                'гильдия', 'pixel', 'hamster', 'combat', 'огонь'
            ],

            'bot': [
                # English terms
                'bot', 'chatbot', 'assistant', 'ai', 'artificial intelligence', 'automation',
                'notification', 'reminder', 'telegram bot', 'command', 'start',

                # Russian terms
                'бот', 'чатбот', 'помощник', 'ии', 'искусственный интеллект', 'автоматизация',
                'уведомление', 'напоминание', 'телеграм бот', 'команда', 'старт',
                'автоматизированный'
            ]
        }

        self._build_category_vectors()

    def _download_nltk_data(self):
        try:
            nltk.data.find('tokenizers/punkt')
            nltk.data.find('corpora/stopwords')
        except LookupError:
            nltk.download('punkt', quiet=True)
            nltk.download('stopwords', quiet=True)

    def _build_category_vectors(self):
        self.vectorizer = TfidfVectorizer(
            lowercase=True,
            stop_words=None,
            ngram_range=(1, 2),
            max_features=1000,
            min_df=1,
            max_df=0.95
        )

        category_docs = []
        self.category_names = []

        for category, keywords in self.categories.items():
            doc = ' '.join(keywords)
            category_docs.append(doc)
            self.category_names.append(category)

        self.category_vectors = self.vectorizer.fit_transform(category_docs)

    def _clean_text(self, text: str) -> str:
        if not text:
            return ""

        text = text.lower()
        text = re.sub(r'http[s]?://(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\\(\\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+', '', text)
        text = re.sub(r'\S+@\S+', '', text)
        text = re.sub(r'[\+]?[1-9]?[0-9]{7,15}', '', text)
        text = re.sub(r'[^\w\s]', ' ', text)
        text = re.sub(r'\s+', ' ', text)

        return text.strip()

    def _lemmatize_text(self, text: str) -> str:
        words = text.split()
        normalized = []

        for word in words:
            if len(word) > 2:
                word = self._basic_stem(word)
                normalized.append(word)

        return ' '.join(normalized)

    def _basic_stem(self, word: str) -> str:
        endings = ['ать', 'ить', 'еть', 'ся', 'сь', 'ый', 'ая', 'ое', 'ые', 'ом', 'ой', 'ем', 'ей', 'ах', 'ов', 'ами', 'ами']
        for ending in endings:
            if word.endswith(ending) and len(word) > len(ending) + 2:
                return word[:-len(ending)]
        return word

    def _extract_text_from_messages(self, messages: List[Dict]) -> str:
        """Extract and combine text from all messages."""
        texts = []

        for msg in messages:
            # Try different possible text fields
            text = ""
            for field in ['text', 'body', 'content', 'text_content', 'message']:
                if field in msg and msg[field]:
                    text = str(msg[field])
                    break

            if text:
                # Clean the text
                cleaned = self._clean_text(text)
                if len(cleaned) > 5:  # Only include meaningful text
                    texts.append(cleaned)

        combined_text = ' '.join(texts)

        if combined_text:
            combined_text = self._lemmatize_text(combined_text)

        return combined_text

    def analyze_chat(self, messages: List[Dict]) -> Optional[str]:
        if not messages:
            return None

        text = self._extract_text_from_messages(messages)

        if not text or len(text.split()) < 3:
            return None

        try:
            text_vector = self.vectorizer.transform([text])
            similarities = cosine_similarity(text_vector, self.category_vectors)[0]

            best_idx = np.argmax(similarities)
            best_score = similarities[best_idx]

            if best_score < 0.1:
                return None

            return self.category_names[best_idx]

        except Exception as e:
            return None

    def get_analysis_details(self, messages: List[Dict]) -> Dict:
        if not messages:
            return {"error": "No messages provided"}

        text = self._extract_text_from_messages(messages)

        if not text:
            return {"error": "No text extracted from messages"}

        try:
            text_vector = self.vectorizer.transform([text])
            similarities = cosine_similarity(text_vector, self.category_vectors)[0]
            results = {
                "extracted_text": text[:200] + "..." if len(text) > 200 else text,
                "text_length": len(text),
                "word_count": len(text.split()),
                "message_count": len(messages),
                "category_scores": {}
            }

            for i, category in enumerate(self.category_names):
                results["category_scores"][category] = float(similarities[i])

            sorted_scores = sorted(results["category_scores"].items(),
                                 key=lambda x: x[1], reverse=True)
            results["ranked_categories"] = sorted_scores

            best_category = sorted_scores[0][0] if sorted_scores[0][1] >= 0.1 else None
            results["best_category"] = best_category
            results["confidence"] = float(sorted_scores[0][1]) if sorted_scores else 0.0

            return results

        except Exception as e:
            return {"error": str(e)}


def main():
    parser = argparse.ArgumentParser(description='Analyze chat messages for semantic tagging')
    parser.add_argument('--messages', type=str, help='JSON string of messages')
    parser.add_argument('--file', type=str, help='JSON file containing messages')
    parser.add_argument('--debug', action='store_true', help='Return detailed analysis')

    args = parser.parse_args()

    # Initialize analyzer
    analyzer = ChatAnalyzer()

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
