<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Благополучатели</h1>
            <p class="text-slate-500 text-sm mt-1">База из <span class="font-bold text-indigo-600">1000+</span> физлиц и компаний</p>
        </div>
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95">
            <span>+</span> <span>Новый получатель</span>
        </button>
    </div>

    <!-- ФИЛЬТР (ТЗ п.1: Удобный поиск/автодополнение) -->
    <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-slate-200 flex items-center gap-2">
        <div class="relative flex-1">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
            <input type="text" 
                   placeholder="Найти по ФИО, ИНН или телефону..." 
                   class="w-full pl-14 pr-6 py-4 bg-transparent border-none focus:ring-0 text-slate-700 placeholder:text-slate-400 font-medium">
        </div>
    </div>

    <!-- ТАБЛИЦА СО ШИММЕРОМ -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
        <app-beneficiaries-list>
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr class="text-[11px] uppercase tracking-[0.15em] text-slate-400 font-black">
                        <th class="px-8 py-5">Тип</th>
                        <th class="px-8 py-5">Наименование / ФИО</th>
                        <th class="px-8 py-5">Контакты</th>
                        <th class="px-8 py-5">Дата регистрации</th>
                        <th class="px-8 py-5 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php for($i=0; $i<8; $i++): ?>
                    <tr>
                        <!-- Тип (Физ/Юр) -->
                        <td class="px-8 py-6">
                            <div class="h-7 shimmer rounded-2xl w-24"></div>
                        </td>
                        <!-- ФИО -->
                        <td class="px-8 py-6">
                            <div class="h-4 shimmer rounded-full w-56"></div>
                        </td>
                        <!-- Контакты (две строки) -->
                        <td class="px-8 py-6">
                            <div class="h-3 shimmer rounded-full w-32 mb-2"></div>
                            <div class="h-3 shimmer rounded-full w-24 opacity-50"></div>
                        </td>
                        <!-- Дата -->
                        <td class="px-8 py-6">
                            <div class="h-4 shimmer rounded-full w-28"></div>
                        </td>
                        <!-- Кнопки -->
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
                <p class="text-slate-400 text-sm font-medium italic animate-bounce">🧠 Диплодок вспоминает имена... (Загрузка Angular)</p>
            </div>
        </app-beneficiaries-list>
    </div>
</div>
<?= $this->endSection() ?>
