<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    
    <!-- HEADER -->
    <div class="max-w-4xl mx-auto text-center space-y-2">
        <h1 class="text-4xl font-black text-slate-800 tracking-tight">Работа с данными Excel</h1>
        <p class="text-slate-500 text-lg">Массовое управление реестром через электронные таблицы</p>
    </div>

        <!-- ТЗ п.3: Напоминалка про валидацию -->
    <div class="mx-auto bg-amber-50 border border-amber-100 p-6 rounded-[2rem] flex items-start space-x-4">
        <span class="text-2xl">⚠️</span>
        <p class="text-sm text-amber-800 leading-relaxed">
            <strong>Внимание:</strong> При импорте система проверяет ФИО благополучателей и типы услуг. Если запись не найдена, она будет пропущена или помечена как ошибочная.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 max-w-6xl mx-auto">
        
        <!-- КАРТОЧКА ИМПОРТА (ТЗ п.3) -->
        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-10 flex flex-col items-center text-center space-y-6">
            <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center text-4xl shadow-inner">📥</div>
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Импорт реестра</h3>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">Загрузите .xlsx файл для автоматического пополнения базы данных. Система проверит валидность полей.</p>
            </div>
            
            <!-- Контейнер для Angular -->
            <app-excel-uploader class="w-full">
                <div class="w-full border-4 border-dashed border-slate-50 rounded-[2rem] p-12 transition-colors hover:border-indigo-100 group">
                    <div class="shimmer h-12 w-40 mx-auto rounded-2xl mb-4"></div>
                    <div class="h-4 shimmer w-full max-w-[200px] mx-auto rounded-full opacity-50"></div>
                </div>
            </app-excel-uploader>

            <a href="/templates/import_template.xlsx" class="text-indigo-600 text-xs font-bold uppercase tracking-widest hover:underline">
                📄 Скачать шаблон (.xlsx)
            </a>
        </div>

        <!-- КАРТОЧКА ЭКСПОРТА (ТЗ п.2) -->
        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-10 flex flex-col items-center text-center space-y-6">
            <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center text-4xl shadow-inner">📤</div>
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Экспорт данных</h3>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">Выгрузка текущего реестра с учётом применённых фильтров и поиска в один клик.</p>
            </div>

    <div class="w-full">
        <a href="<?= base_url('api/export') ?>" 
           class="block w-full py-5 bg-emerald-600 text-white rounded-[2rem] font-black uppercase tracking-widest hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-100 transition-all active:scale-95 text-center">
            Сгенерировать .XLSX
        </a>
        <p class="mt-4 text-[10px] text-slate-400 uppercase font-bold tracking-tighter italic">
            Данные будут выгружены из PostgreSQL
        </p>
    </div>

            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-tighter italic">Формат файла: Microsoft Excel (.xlsx)</p>
        </div>

    </div>

</div>
<?= $this->endSection() ?>
