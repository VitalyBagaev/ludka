<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Пул скинов</h1>
                <p class="ludo-page-sub mb-0">Добавление и редактирование предметов, цен и редкости</p>
            </div>
            <span class="ludo-stat-pill">Всего скинов: <strong>{{ skins.length }}</strong></span>
        </div>

        <div class="ludo-card p-4 mb-4" id="skin-form-card" style="border: 1px solid rgba(240, 180, 41, 0.25);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="ludo-section-label mb-0">{{ editId ? 'Редактировать скин #' + editId : 'Добавить новый скин' }}</div>
                <button v-if="editId" type="button" class="btn btn-sm btn-ludo-outline" @click="resetForm">Отмена</button>
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <input v-model.trim="form.weapon" class="form-control ludo-form-control" placeholder="Оружие (напр. AK-47)" />
                </div>
                <div class="col-md-3">
                    <input v-model.trim="form.name" class="form-control ludo-form-control" placeholder="Название (напр. Redline)" />
                </div>
                <div class="col-md-2">
                    <select v-model="form.rarity" class="form-select ludo-form-control">
                        <option v-for="r in rarityOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input v-model.number="form.price" type="number" min="1" class="form-control ludo-form-control" placeholder="Цена ₽" />
                </div>
                <div class="col-md-2">
                    <label class="btn btn-ludo-outline w-100 mb-0 d-flex align-items-center justify-content-center" style="height: 38px;">
                        {{ imageFile ? 'Файл выбран ✓' : '📷 Картинка' }}
                        <input type="file" accept="image/*" class="d-none" @change="onImagePick" />
                    </label>
                </div>
            </div>
            <div v-if="previewImage" class="mt-3 text-center">
                <img :src="previewImage" style="height: 80px; object-fit: contain;" @error="imgError($event, 'skin')" />
            </div>
            <div v-if="message" class="ludo-alert ludo-alert-danger mt-3 mb-0">{{ message }}</div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-ludo-primary px-4" :disabled="saving" @click="save">
                    {{ saving ? 'Сохраняем...' : editId ? 'Сохранить изменения' : '+ Добавить скин' }}
                </button>
            </div>
        </div>
        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-4">
                <input v-model.trim="searchQuery" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск по оружию, названию или цене..." />
            </div>
            <div class="col-sm-6 col-md-4">
                <select v-model="rarityFilter" class="form-select form-select-sm ludo-form-control">
                    <option value="all">Все редкости ({{ skins.length }})</option>
                    <option v-for="r in rarityOptions" :key="'f-' + r.value" :value="r.value">{{ r.label }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-4">
                <select v-model="sortBy" class="form-select form-select-sm ludo-form-control">
                    <option value="price_desc">Сначала дорогие</option>
                    <option value="price_asc">Сначала дешёвые</option>
                    <option value="name_asc">По названию (А–Я)</option>
                </select>
            </div>
        </div>
        <div v-if="!filteredSkins.length" class="ludo-card p-5 text-center">
            <div class="text-secondary">По вашему запросу скинов не найдено</div>
        </div>

        <div v-else class="row g-3">
            <div v-for="s in filteredSkins" :key="s.id" class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="ludo-skin-card h-100 d-flex flex-column justify-content-between text-center" :class="rarityClass ? rarityClass[s.rarity] : ''">
                    <div>
                        <img :src="skinImg(s.image)" style="height: 64px; object-fit: contain;" class="mb-2" @error="imgError($event, 'skin')" />
                        <div class="small fw-semibold text-truncate">{{ s.weapon }}</div>
                        <div class="small text-secondary text-truncate">{{ s.name }}</div>
                        <div class="ludo-case-price my-1">{{ s.price }} ₽</div>
                    </div>
                    <div class="d-flex justify-content-center gap-1 mt-2">
                        <button type="button" class="btn btn-ludo-outline btn-sm flex-grow-1" @click="editSkin(s)">✎ Изменить</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeSkin(s.id)">×</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { rarityClass, rarityLabel, rarityOrder } from '../helpers/images-meta.js';

export default {
    name: 'AdminSkinsPage',
    props: ['datasend', 'datasendJson', 'skinImg', 'imgError'],
    data() {
        return {
            skins: [],
            editId: null,
            saving: false,
            message: null,
            imageFile: null,
            previewImage: null,
            searchQuery: '',
            rarityFilter: 'all',
            sortBy: 'price_desc',
            rarityClass,
            form: { name: '', weapon: '', rarity: 'consumer', price: 100 },
        };
    },
    computed: {
        rarityOptions() {
            return rarityOrder.map((key) => ({ value: key, label: rarityLabel[key] || key }));
        },
        filteredSkins() {
            const q = this.searchQuery.toLowerCase();
            let list = [...this.skins];
            if (this.rarityFilter !== 'all') {
                list = list.filter((s) => s.rarity === this.rarityFilter);
            }
            if (q) {
                list = list.filter((s) => s.name.toLowerCase().includes(q) || s.weapon.toLowerCase().includes(q) || String(s.price).includes(q));
            }
            if (this.sortBy === 'price_asc') return list.sort((a, b) => a.price - b.price);
            if (this.sortBy === 'name_asc') return list.sort((a, b) => a.weapon.localeCompare(b.weapon) || a.name.localeCompare(b.name));
            return list.sort((a, b) => b.price - a.price);
        },
    },
    mounted() {
        this.getSkins();
    },
    methods: {
        getSkins() {
            this.datasend('admin/skins').then((result) => {
                if (result) this.skins = result;
            });
        },
        onImagePick(event) {
            const file = event.target.files ? event.target.files.item(0) : null;
            this.imageFile = file || null;
            this.previewImage = file ? URL.createObjectURL(file) : this.previewImage;
        },
        resetForm() {
            this.editId = null;
            this.message = null;
            this.imageFile = null;
            this.previewImage = null;
            this.form = { name: '', weapon: '', rarity: 'consumer', price: 100 };
        },
        save() {
            this.saving = true;
            this.message = null;
            const route = this.editId ? 'admin/skins/' + this.editId : 'admin/skins';
            const done = (result) => {
                this.saving = false;
                if (!result) { this.message = 'Ошибка сохранения'; return; }
                if (result.message && !result.id) { this.message = result.message; return; }
                if (result.errors) { this.message = Object.values(result.errors).flat().join(' '); return; }
                this.resetForm();
                this.getSkins();
            };
            if (this.imageFile) {
                const fd = new FormData();
                fd.append('name', this.form.name);
                fd.append('weapon', this.form.weapon);
                fd.append('rarity', this.form.rarity);
                fd.append('price', String(this.form.price));
                fd.append('image', this.imageFile);
                this.datasend(route, 'POST', fd).then(done);
            } else {
                this.datasendJson(route, 'POST', this.form).then(done);
            }
        },
        editSkin(s) {
            this.editId = s.id;
            this.imageFile = null;
            this.previewImage = this.skinImg(s.image);
            this.form = { name: s.name, weapon: s.weapon, rarity: s.rarity, price: s.price };
            document.getElementById('skin-form-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        removeSkin(id) {
            if (!confirm('Удалить скин из пула?')) return;
            this.datasend('admin/skins/' + id, 'DELETE').then(() => this.getSkins());
        },
    },
};
</script>
