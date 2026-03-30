<?php
helper('system'); // CI4 сам найдет system_helper.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🦕 DIPLO-DOC | Система учёта услуг</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        /* Скроллбар для СДВГ-аккуратности */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .shimmer {
            background: linear-gradient(to right, #f1f5f9 8%, #e2e8f0 18%, #f1f5f9 33%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900">

    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="w-72 bg-[#1e1b4b] text-white flex flex-col shadow-2xl z-10">
            <!-- Logo -->
            <div class="p-8 pb-12 flex items-center space-x-3">
                <span class="text-3xl font-black italic tracking-tighter text-indigo-400">🦕 DIPLO</span>
                <span class="text-3xl font-black italic tracking-tighter text-white">DOC</span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-4 space-y-1.5">
                <a href="<?= base_url('dashboard') ?>" 
                   class="group flex items-center space-x-3 p-3.5 rounded-2xl transition-all hover:bg-indigo-800/50 <?= is_page_active('services') ?>">
                    <span class="text-xl">📊</span>
                    <span class="font-semibold tracking-wide text-sm uppercase">Реестр услуг</span>
                </a>

                <a href="<?= base_url('beneficiaries') ?>" 
                   class="group flex items-center space-x-3 p-3.5 rounded-2xl transition-all hover:bg-indigo-800/50 <?= is_page_active('beneficiaries') ?>">
                    <span class="text-xl">👥</span>
                    <span class="font-semibold tracking-wide text-sm uppercase text-opacity-80">Благополучатели</span>
                </a>

                <a href="<?= base_url('import') ?>" 
                   class="group flex items-center space-x-3 p-3.5 rounded-2xl transition-all hover:bg-indigo-800/50 <?= is_page_active('import') ?>">
                    <span class="text-xl">📥</span>
                    <span class="font-semibold tracking-wide text-sm uppercase text-opacity-80">Импорт/Экспорт</span>
                </a>
            </nav>                



            <!-- Bottom Badge -->
            <div class="p-6">
                <div class="bg-indigo-900/40 border border-indigo-800 p-4 rounded-3xl">
                    <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mb-1 text-center">Статус системы</p>
                    <div class="flex items-center justify-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs text-indigo-100 font-medium">Postgres Connected</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
            <!-- Top Header -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-10">
                <div class="flex items-center space-x-2 text-slate-400 text-sm">
                    <span>Admin</span> <span>/</span> <span class="text-slate-800 font-bold"><?= $title ?? 'Управление' ?></span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-800 leading-none">Бро Разработчик</p>
                        <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-tighter mt-1">Fullstack Dev</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-blue-500 rounded-2xl shadow-md flex items-center justify-center text-white font-bold">B</div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-10 mat-app-background indigo-pink-theme">
                <div class="max-w-7xl mx-auto">

                    <app-root data-page="<?= get_current_angular_page() ?>">
                        <?= $this->renderSection('content') ?>
                    </app-root>   
                    
                </div>
            </main>
        </div>
    </div>
<!-- Подключаем скрипты Ангуляра из папки public/dist -->
<script src="<?= base_url('dist/browser/polyfills.js') ?>" type="module"></script>
<script src="<?= base_url('dist/browser/main.js') ?>" type="module"></script>

<script>
    window.APP_CONFIG = {
        limits: <?php echo json_encode([
            'upload_max' => dino_parse_size(ini_get('upload_max_filesize')),
            'display'    => ini_get('upload_max_filesize')
        ]); ?>,
        statuses: <?= json_encode(\App\Entities\Service::getStatusList()) ?>
    };
</script>

</body>
</html>
