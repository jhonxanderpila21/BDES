<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'BDES')); ?></title>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 min-h-screen">
        <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-36 -right-36 w-96 h-96 bg-blue-100/60 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-36 -left-36 w-96 h-96 bg-indigo-100/50 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10 w-full max-w-md">
                
                <div class="text-center mb-8">
                    <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center gap-3 group">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-xl shadow-md shadow-blue-500/25 flex items-center justify-center text-xl transition-transform group-hover:scale-105">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="text-left">
                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 leading-tight">BDES</h1>
                            <p class="text-xs font-medium text-gray-500">Disaster Asset Management</p>
                        </div>
                    </a>
                </div>

                
                <div class="bg-white border border-gray-200/80 shadow-lg shadow-blue-500/5 rounded-2xl p-8 sm:p-10">
                    <?php echo e($slot); ?>

                </div>

                
                <p class="text-center text-xs text-gray-500 mt-6">
                    &copy; <?php echo e(date('Y')); ?> Barangay Disaster Emergency Services. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>

<?php /**PATH C:\Capstone\laravel\CapstoneFinal\BDES\resources\views/layouts/guest.blade.php ENDPATH**/ ?>