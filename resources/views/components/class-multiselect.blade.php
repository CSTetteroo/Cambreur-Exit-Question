@props([
    'name' => 'class_id[]',
    'classes' => collect(),
    'selected' => [],
    'placeholder' => 'Selecteer klassen',
])

@php
    $options = ($classes instanceof \Illuminate\Support\Collection) ? $classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() : collect();
    $selectedIds = is_array($selected) ? $selected : (method_exists($selected, 'toArray') ? $selected->toArray() : []);
@endphp

<div x-data="classMultiSelect({
        initialSelected: @js($selectedIds),
        options: @js($options),
        name: @js($name),
        placeholder: @js($placeholder)
    })" class="w-full">
    <!-- Hidden inputs for form submission -->
    <template x-for="id in selected" :key="'hid-'+id">
        <input type="hidden" :name="name" :value="id">
    </template>

    <div class="space-y-2">
        <!-- Selected tags -->
        <div class="flex flex-wrap gap-2">
            <template x-if="selectedItems.length === 0">
                <span class="text-xs text-gray-400" x-text="placeholder"></span>
            </template>
            <template x-for="item in selectedItems" :key="'tag-'+item.id">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-700/60 border border-gray-600 text-xs">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span x-text="item.name"></span>
                    <button type="button" class="ml-1 text-gray-300 hover:text-gray-100" @click="toggle(item.id)" aria-label="Verwijder">&times;</button>
                </span>
            </template>
            <button x-ref="trigger" type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 text-xs" @click="toggleOpen()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Toevoegen</span>
            </button>
        </div>
        <!-- Teleported Dropdown -->
        <template x-teleport="body">
            <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open=false" @click.outside="open=false"
                 :style="`position:fixed; top:${dropdownY}px; left:${dropdownX}px; width:${dropdownW}px;`"
                 class="z-[999] rounded border border-gray-600 bg-gray-800 shadow-xl">
                <div class="p-2 border-b border-gray-700 flex items-center gap-2">
                    <input type="text" x-model="search" placeholder="Zoeken..." class="flex-1 px-2 py-1 rounded bg-gray-700 border border-gray-600 text-sm">
                    <button type="button" @click="clearAll()" class="text-xs px-2 py-1 rounded bg-gray-700 hover:bg-gray-600">Wis</button>                </div>
                <ul class="max-h-64 overflow-auto py-1">
                    <template x-for="opt in filteredOptions" :key="'opt-'+opt.id">
                        <li>
                            <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-700 flex items-center justify-between" @click="toggle(opt.id)">
                                <span x-text="opt.name"></span>
                                <template x-if="isSelected(opt.id)">
                                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </template>
                            </button>
                        </li>
                    </template>
                    <template x-if="filteredOptions.length === 0">
                        <li class="px-3 py-2 text-sm text-gray-400">Geen resultaten</li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
</div>

<script>
    function classMultiSelect({ initialSelected = [], options = [], name = 'class_id[]', placeholder = 'Selecteer klassen' }){
        return {
            open: false,
            dropdownX: 0,
            dropdownY: 0,
            dropdownW: 260,
            search: '',
            name,
            placeholder,
            options,
            selected: [...initialSelected],
            get selectedItems(){
                const map = new Map(this.options.map(o => [o.id, o]));
                return this.selected.map(id => map.get(id)).filter(Boolean);
            },
            filteredOptions(){
                const q = this.search.toLowerCase();
                return this.options.filter(o => o.name.toLowerCase().includes(q));
            },
            isSelected(id){
                return this.selected.includes(id);
            },
            toggle(id){
                const idx = this.selected.indexOf(id);
                if(idx >= 0){
                    this.selected.splice(idx,1);
                } else {
                    this.selected.push(id);
                }
            },
            toggleOpen(){
                if(!this.open){
                    this.computePosition();
                    this.open = true;
                } else {
                    this.open = false;
                }
            },
            computePosition(){
                this.$nextTick(() => {
                    const btn = this.$refs.trigger;
                    if(!btn) return;
                    const rect = btn.getBoundingClientRect();
                    this.dropdownX = rect.left;
                    this.dropdownY = rect.bottom + 4; // little gap
                    this.dropdownW = rect.width < 220 ? 220 : rect.width; // min width
                });
            },
            selectAll(){
                this.selected = this.options.map(o => o.id);
            },
            clearAll(){
                this.selected = [];
            }
        }
    }
</script>
