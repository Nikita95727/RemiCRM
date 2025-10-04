<!--[if BLOCK]><![endif]--><?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            <!--[if BLOCK]><![endif]--><?php if($paginator->onFirstPage()): ?>
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5 rounded-md">
                    <?php echo __('pagination.previous'); ?>

                </span>
            <?php else: ?>
                <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-500 focus:outline-none focus:ring ring-slate-300 focus:border-indigo-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150">
                    <?php echo __('pagination.previous'); ?>

                </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($paginator->hasMorePages()): ?>
                <button wire:click="nextPage" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-500 focus:outline-none focus:ring ring-slate-300 focus:border-indigo-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150">
                    <?php echo __('pagination.next'); ?>

                </button>
            <?php else: ?>
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5 rounded-md">
                    <?php echo __('pagination.next'); ?>

                </span>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center">
            <div class="mr-8">
                <p class="text-sm text-slate-700 leading-5">
                    <?php echo __('Showing'); ?>

                    <!--[if BLOCK]><![endif]--><?php if($paginator->firstItem()): ?>
                        <span class="font-medium"><?php echo e($paginator->firstItem()); ?></span>
                        <?php echo __('to'); ?>

                        <span class="font-medium"><?php echo e($paginator->lastItem()); ?></span>
                    <?php else: ?>
                        <?php echo e($paginator->count()); ?>

                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php echo __('of'); ?>

                    <span class="font-medium"><?php echo e($paginator->total()); ?></span>
                    <?php echo __('results'); ?>

                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse shadow-sm rounded-md">
                    
                    <!--[if BLOCK]><![endif]--><?php if($paginator->onFirstPage()): ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    <?php else: ?>
                        <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-l-md leading-5 hover:text-slate-500 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-300 focus:border-indigo-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        
                        <!--[if BLOCK]><![endif]--><?php if(is_string($element)): ?>
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 cursor-default leading-5"><?php echo e($element); ?></span>
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        
                        <!--[if BLOCK]><![endif]--><?php if(is_array($element)): ?>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <!--[if BLOCK]><![endif]--><?php if($page == $paginator->currentPage()): ?>
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-indigo-600 border border-indigo-600 cursor-default leading-5 font-semibold"><?php echo e($page); ?></span>
                                    </span>
                                <?php else: ?>
                                    <button wire:click="gotoPage(<?php echo e($page); ?>)" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 hover:text-slate-500 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-300 focus:border-indigo-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>">
                                        <?php echo e($page); ?>

                                    </button>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <!--[if BLOCK]><![endif]--><?php if($paginator->hasMorePages()): ?>
                        <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-r-md leading-5 hover:text-slate-500 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-300 focus:border-indigo-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    <?php else: ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-400 bg-white border border-slate-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </span>
            </div>
        </div>
    </nav>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/vendor/pagination/custom-tailwind.blade.php ENDPATH**/ ?>