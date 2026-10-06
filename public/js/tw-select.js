document.addEventListener('DOMContentLoaded', () => {
    function initTwSelect() {
        const selectContainers = document.querySelectorAll('.tw-select-container');

        selectContainers.forEach(container => {
            // Jika sudah diinisialisasi, lewati
            if (container.dataset.initialized === 'true') return;
            container.dataset.initialized = 'true';

            const nativeSelect = container.querySelector('select');
            const trigger = container.querySelector('.tw-select-trigger');
            const triggerText = container.querySelector('.tw-select-text');
            const dropdown = container.querySelector('.tw-select-dropdown');
            const searchInput = container.querySelector('.tw-select-search');
            const list = container.querySelector('.tw-select-list');
            const emptyState = container.querySelector('.tw-select-empty');

            if (!nativeSelect || !trigger || !dropdown) return;

            let isOpen = false;
            let options = [];

            // Ekstrak Opsi dari Select Asli
            function extractOptions() {
                options = [];
                Array.from(nativeSelect.options).forEach(opt => {
                    if (opt.value) { // Lewati opsi placeholder kosong
                        options.push({ value: opt.value, text: opt.text, selected: opt.selected });
                    }
                });
            }
            extractOptions();

            // Set initial text jika ada yang terpilih
            function updateTriggerText() {
                const selectedOpt = Array.from(nativeSelect.options).find(o => o.value === nativeSelect.value && o.value !== '');
                if (selectedOpt) {
                    triggerText.textContent = selectedOpt.text;
                    triggerText.classList.remove('text-slate-500', 'text-gray-500');
                    triggerText.classList.add('text-slate-800');
                } else {
                    triggerText.textContent = nativeSelect.options[0]?.text || 'Pilih...';
                    triggerText.classList.add('text-slate-500', 'text-gray-500');
                    triggerText.classList.remove('text-slate-800');
                }
            }
            updateTriggerText();

            // Fungsi Render List
            function renderList(filter = '') {
                list.innerHTML = '';
                const filtered = options.filter(opt => opt.text.toLowerCase().includes(filter.toLowerCase()));
                
                if (filtered.length === 0) {
                    list.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                } else {
                    list.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                    
                    filtered.forEach((opt, index) => {
                        const li = document.createElement('li');
                        li.className = `px-3 py-2 text-sm rounded-md cursor-pointer transition-colors flex items-center justify-between ${opt.value === nativeSelect.value ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-100'}`;
                        li.innerHTML = `
                            <span>${opt.text}</span>
                            ${opt.value === nativeSelect.value ? '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : ''}
                        `;
                        
                        // Event Click untuk memilih
                        li.addEventListener('click', (e) => {
                            e.stopPropagation();
                            selectOption(opt);
                        });
                        
                        list.appendChild(li);
                    });
                }
            }

            // Fungsi Memilih Opsi
            function selectOption(opt) {
                nativeSelect.value = opt.value;
                nativeSelect.dispatchEvent(new Event('change')); // Trigger event asli
                updateTriggerText();
                closeDropdown();
            }

            // Toggle Dropdown
            function openDropdown() {
                // Tutup dropdown lain yang terbuka
                document.querySelectorAll('.tw-select-dropdown').forEach(d => {
                    if (d !== dropdown && !d.classList.contains('hidden')) {
                        d.classList.add('hidden', 'opacity-0');
                        d.classList.remove('opacity-100');
                    }
                });

                extractOptions(); // Refresh option in case it changed dynamically
                isOpen = true;
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.remove('opacity-0');
                    dropdown.classList.add('opacity-100');
                }, 10);
                
                trigger.classList.add('ring-2', 'ring-blue-500/20', 'border-blue-500');
                searchInput.value = '';
                renderList();
                searchInput.focus();
            }

            function closeDropdown() {
                isOpen = false;
                dropdown.classList.remove('opacity-100');
                dropdown.classList.add('opacity-0');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
                
                trigger.classList.remove('ring-2', 'ring-blue-500/20', 'border-blue-500');
            }

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (isOpen) closeDropdown();
                else openDropdown();
            });

            // Pencarian
            searchInput.addEventListener('input', (e) => {
                renderList(e.target.value);
            });
            
            // Prevent form submit on search enter
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            // Klik di luar untuk menutup
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target) && isOpen) {
                    closeDropdown();
                }
            });
            
            // Update UI jika value diubah via JS
            nativeSelect.addEventListener('change', () => {
                updateTriggerText();
            });
        });
    }

    // Initialize
    initTwSelect();

    // Export to window so it can be called after dynamic content loads
    window.initTwSelect = initTwSelect;
});
