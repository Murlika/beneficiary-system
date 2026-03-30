<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>


<app-service-table>
<div class="space-y-6">
    
    <!-- 1. HEADER С ДЕЙСТВИЯМИ -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Единый реестр услуг</h1>
            <p class="text-slate-500 text-sm mt-1">Найдено <span class="font-bold text-indigo-600">1,240</span> записей за всё время</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- ТЗ п.2: Экспорт (Добавим индикатор загрузки потом) -->
            <button class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-2xl font-bold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm active:scale-95">
                <span class="text-lg">📥</span>
                <span class="text-sm uppercase tracking-wide">Экспорт .xlsx</span>
            </button>
            
            <!-- ТЗ п.5: Добавление новой записи -->
            <button class="flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95">
                <span class="text-xl">+</span>
                <span class="text-sm uppercase tracking-wide">Добавить услугу</span>
            </button>
        </div>
    </div>

    <!-- 2. БЛОК ФИЛЬТРАЦИИ (ТЗ п.1) -->
    <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-slate-200 flex flex-wrap lg:flex-nowrap items-center gap-2">
        <!-- Поиск по ФИО (Main Input) -->
        <div class="relative flex-1 min-w-[300px]">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
            <input type="text" 
                   placeholder="Поиск по ФИО или названию услуги..." 
                   class="w-full pl-14 pr-6 py-4 bg-transparent border-none focus:ring-0 text-slate-700 placeholder:text-slate-400 font-medium">
        </div>

        <!-- Разделитель -->
        <div class="hidden lg:block w-px h-10 bg-slate-100"></div>

        <!-- Фильтр Статуса -->
        <select class="bg-transparent border-none focus:ring-0 px-6 py-4 text-slate-600 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
            <option value="">Все статусы</option>
            <option value="new">🆕 Новые</option>
            <option value="process">⏳ В работе</option>
            <option value="done">✅ Завершены</option>
        </select>

        <!-- Кнопка "Применить" (для тех, кто не любит Live-search) -->
        <button class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-bold text-sm uppercase tracking-wider hover:bg-indigo-600 transition-all ml-auto mr-1">
            Найти
        </button>
    </div>

    <!-- 3. ТАБЛИЦА (Контейнер для Angular) -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
        <app-services-registry>
            <!-- Скелетон: показываем пока Angular "прогревается" -->
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr class="text-[11px] uppercase tracking-[0.15em] text-slate-400 font-black">
                        <th class="px-8 py-5">Дата</th>
                        <th class="px-8 py-5">Благополучатель</th>
                        <th class="px-8 py-5">Услуга</th>
                        <th class="px-8 py-5">Статус</th>
                        <th class="px-8 py-5 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php for($i=0; $i<6; $i++): ?>
                    <tr>
                        <td class="px-8 py-6">
                            <div class="h-4 shimmer rounded-full w-20"></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="h-4 shimmer rounded-full w-48 mb-2"></div>
                            <div class="h-3 shimmer rounded-full w-32 opacity-50"></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="h-4 shimmer rounded-full w-32"></div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="h-7 shimmer rounded-2xl w-24"></div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="inline-flex gap-2">
                                <div class="w-8 h-8 shimmer rounded-lg"></div>
                                <div class="w-8 h-8 shimmer rounded-lg"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>

            </table>
            <div class="p-8 text-center bg-slate-50/30 border-t border-slate-50">
                <p class="text-slate-400 text-sm font-medium italic animate-bounce">🦖 Диплодок разминает шею... (Загрузка Angular)</p>
            </div>
        </app-services-registry>
    </div>
</div>
                    </app-service-table>
<?= $this->endSection() ?>
