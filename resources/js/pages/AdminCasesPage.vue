<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Управление кейсами</h1>
                <p class="ludo-page-sub mb-0">Создание кейсов, настройка состава скинов, шансов выпадения и меток «Хит»</p>
            </div>
            <span class="ludo-stat-pill">Всего кейсов: <strong>{{ cases.length }}</strong></span>
        </div>

        <div class="ludo-card p-4 mb-4" id="case-form-card" style="border: 1px solid rgba(240, 180, 41, 0.25);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="ludo-section-label mb-0">{{ editId ? 'Редактировать кейс #' + editId : 'Создать новый кейс' }}</div>
                <button v-if="editId" type="button" class="btn btn-sm btn-ludo-outline" @click="resetForm">Отмена</button>
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input v-model.trim="form.name" class="form-control ludo-form-control" placeholder="Название кейса" />
                </div>
                <div class="col-md-2">
                    <input v-model.number="form.price" type="number" min="1" class="form-control ludo-form-control" placeholder="Цена ₽" />
                </div>
                <div class="col-md-3">
                    <select v-model="form.category" class="form-select ludo-form-control">
                        <option v-for="(label, key) in categoryLabels" :key="key" v-show="key !== 'all'" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="d-flex align-items-center gap-2 small mb-0" style="cursor: pointer;">
                        <input v-model="form.featured" type="checkbox" class="form-check-input mt-0" />
                        <span>🔥 Хит (Featured)</span>
                    </label>
                </div>
                <div class="col-md-2">
                    <label class="btn btn-ludo-outline w-100 mb-0 d-flex align-items-center justify-content-center" style="height: 38px;">
                        {{ imageFile ? 'Обложка ✓' : '📷 Обложка' }}
                        <input type="file" accept="image/*" class="d-none" @change="onImagePick" />
                    </label>
                </div>
            </div>
            <div v-if="previewImage" class="mt-3 text-center">
                <img :src="previewImage" style="height: 84px; object-fit: contain;" @error="imgError($event, 'case')" />
            </div>
            <div v-if="form.price >= 5000" class="small mt-2" :class="cheapTierCount >= 10 ? 'text-success' : 'text-warning'">
                Дешёвых скинов (50%–80% от цены): <strong>{{ cheapTierCount }} / 10</strong>
                <span v-if="cheapTierCount < 10"> — добавьте ещё {{ 10 - cheapTierCount }}</span>
            </div>
            <div v-if="message" class="ludo-alert ludo-alert-danger mt-3 mb-0">{{ message }}</div>

            <div class="mt-4 pt-3 border-top border-secondary-subtle">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="ludo-section-label mb-0">Скины в кейсе ({{ selectedSkins.length }})</div>
                    <span v-if="selectedSkins.length" class="small text-secondary">Сумма весов: <strong>{{ totalDropWeight }}</strong></span>
                </div>

                <div v-if="selectedSkins.length" class="mb-3 d-flex flex-column gap-2">
                    <div v-for="item in selectedSkins" :key="'sel-' + item.skin_id" class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="small">
                            <span class="text-secondary">{{ item.weapon }}</span>
                            <span class="mx-1">|</span>
                            <span class="fw-semibold">{{ item.name }}</span>
                            <span class="ludo-case-price ms-2">{{ item.price }} ₽</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-secondary">Вес:</span>
                            <input v-model.number="item.drop_weight" type="number" min="1" class="form-control form-control-sm ludo-form-control" style="width: 75px;" />
                            <span class="badge bg-dark border border-secondary-subtle text-info" style="min-width: 58px;">{{ calcSkinChance(item.drop_weight) }}%</span>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="removeSkin(item.skin_id)">×</button>
                        </div>
                    </div>
                </div>
                <div v-else class="small text-secondary mb-3">Скины не выбраны — добавьте их из списка ниже</div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input v-model.trim="skinSearch" class="form-control form-control-sm ludo-form-control" placeholder="Поиск скина в пуле по названию, оружию или цене..." />
                    </div>
                </div>

                <div class="ludo-table-wrap" style="max-height: 250px; overflow-y: auto">
                    <div class="table-responsive">
                        <table class="table ludo-table table-sm mb-0">
                            <thead>
                                <tr><th>Скин</th><th>Цена</th><th class="text-end">Добавить</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="skin in filteredSkins" :key="skin.id">
                                    <td class="small">{{ skin.weapon }} | {{ skin.name }}</td>
                                    <td class="ludo-case-price small">{{ skin.price }} ₽</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-ludo-outline" :disabled="isSkinSelected(skin.id)" @click="addSkin(skin)">
                                            {{ isSkinSelected(skin.id) ? 'В кейсе ✓' : '+ Добавить' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredSkins.length">
                                    <td colspan="3" class="text-secondary text-center py-3">Ничего не найдено</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-ludo-primary px-4" :disabled="saving" @click="save">
                        {{ saving ? 'Сохраняем...' : editId ? 'Сохранить кейс' : '+ Создать кейс' }}
                    </button>
                </div>
            </div>
        </div>
        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-5">
                <input v-model.trim="caseSearch" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск кейса по названию..." />
            </div>
            <div class="col-md-4">
                <select v-model="caseCategoryFilter" class="form-select form-select-sm ludo-form-control">
                    <option value="all">Все категории ({{ cases.length }})</option>
                    <option v-for="(label, key) in categoryLabels" :key="'cf-' + key" v-show="key !== 'all'" :value="key">{{ label }}</option>
                </select>
            </div>
        </div>

        <div class="ludo-table-wrap">
            <div class="table-responsive">
                <table class="table ludo-table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Кейс</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Хит</th>
                            <th>Скинов</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredCases.length">
                            <td colspan="6" class="text-center text-secondary py-4">Кейсы не найдены</td>
                        </tr>
                        <tr v-for="c in filteredCases" :key="c.id">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img :src="caseImg(c.image)" style="width: 38px; height: 38px; object-fit: contain;" @error="imgError($event, 'case')" />
                                    <span class="fw-semibold">{{ c.name }}</span>
                                </div>
                            </td>
                            <td class="small">{{ categoryLabels[c.category] || c.category }}</td>
                            <td class="ludo-case-price">{{ c.price }} ₽</td>
                            <td>{{ c.featured ? '🔥' : '—' }}</td>
                            <td>{{ c.skins_count ?? 0 }}</td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-ludo-outline me-1" @click="editCase(c)">✎</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeCase(c.id)">×</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
import { categoryLabels } from '../helpers/game.js';

export default {
    name: 'AdminCasesPage',
    props: ['datasend', 'datasendJson', 'caseImg', 'imgError'],
    data() {
        return {
            cases: [],
            allSkins: [],
            selectedSkins: [],
            skinSearch: '',
            caseSearch: '',
            caseCategoryFilter: 'all',
            editId: null,
            saving: false,
            message: null,
            imageFile: null,
            previewImage: null,
            categoryLabels,
            form: { name: '', price: 100, category: 'serial', featured: false },
        };
    },
    computed: {
        totalDropWeight() {
            return this.selectedSkins.reduce((sum, s) => sum + (Number(s.drop_weight) || 0), 0);
        },
        filteredCases() {
            const q = this.caseSearch.toLowerCase();
            return this.cases.filter((c) => {
                if (this.caseCategoryFilter !== 'all' && c.category !== this.caseCategoryFilter) return false;
                if (q && !c.name.toLowerCase().includes(q)) return false;
                return true;
            });
        },
        cheapTierCount() {
            if (this.form.price < 5000) return 0;
            const min = Math.max(500, Math.floor(this.form.price * 0.5));
            const max = Math.floor(this.form.price * 0.8);
            return this.selectedSkins.filter((item) => item.price >= min && item.price <= max).length;
        },
        filteredSkins() {
            const q = this.skinSearch.trim().toLowerCase();
            if (!q) return this.allSkins.slice(0, 80);
            return this.allSkins
                .filter((skin) => skin.name.toLowerCase().includes(q) || skin.weapon.toLowerCase().includes(q) || String(skin.price).includes(q))
                .slice(0, 80);
        },
    },
    mounted() {
        this.getCases();
        this.getSkins();
    },
    methods: {
        calcSkinChance(weight) {
            if (!this.totalDropWeight) return '0.0';
            return (((Number(weight) || 0) / this.totalDropWeight) * 100).toFixed(1);
        },
        getCases() {
            this.datasend('admin/cases').then((result) => {
                if (result) this.cases = result;
            });
        },
        getSkins() {
            this.datasend('admin/skins').then((result) => {
                if (result) this.allSkins = result;
            });
        },
        isSkinSelected(id) {
            return this.selectedSkins.some((item) => item.skin_id === id);
        },
        addSkin(skin) {
            if (this.isSkinSelected(skin.id)) return;
            this.selectedSkins.push({ skin_id: skin.id, drop_weight: 100, name: skin.name, weapon: skin.weapon, price: skin.price });
        },
        removeSkin(id) {
            this.selectedSkins = this.selectedSkins.filter((item) => item.skin_id !== id);
        },
        resetForm() {
            this.editId = null;
            this.message = null;
            this.selectedSkins = [];
            this.skinSearch = '';
            this.imageFile = null;
            this.previewImage = null;
            this.form = { name: '', price: 100, category: 'serial', featured: false };
        },
        onImagePick(event) {
            const file = event.target.files ? event.target.files.item(0) : null;
            this.imageFile = file || null;
            this.previewImage = file ? URL.createObjectURL(file) : this.previewImage;
        },
        editCase(item) {
            this.message = null;
            this.datasend('admin/cases/' + item.id).then((result) => {
                if (!result || result.message) {
                    this.message = result?.message || 'Не удалось загрузить кейс';
                    return;
                }
                this.editId = result.id;
                this.form = { name: result.name, price: result.price, category: result.category, featured: !!result.featured };
                this.imageFile = null;
                this.previewImage = this.caseImg ? this.caseImg(result.image) : result.image;
                this.selectedSkins = (result.skins || []).map((skin) => ({
                    skin_id: skin.skin_id || skin.id,
                    drop_weight: skin.drop_weight || 100,
                    name: skin.name,
                    weapon: skin.weapon,
                    price: skin.price,
                }));
                document.getElementById('case-form-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        save() {
            if (!this.form.name.trim()) { this.message = 'Укажите название кейса'; return; }
            this.saving = true;
            this.message = null;
            const route = this.editId ? 'admin/cases/' + this.editId : 'admin/cases';
            const payload = {
                ...this.form,
                skins: this.selectedSkins.map((item) => ({ skin_id: item.skin_id, drop_weight: item.drop_weight })),
            };
            this.datasendJson(route, 'POST', payload).then((result) => {
                if (!result || result.message || result.errors) {
                    this.saving = false;
                    if (!result) this.message = 'Ошибка сохранения';
                    else if (result.message) this.message = result.message;
                    else if (result.errors) this.message = Object.values(result.errors).flat().join(' ');
                    return;
                }
                const caseId = result.id || this.editId;
                if (this.imageFile && caseId) {
                    const fd = new FormData();
                    fd.append('image', this.imageFile);
                    this.datasend('admin/cases/' + caseId + '/image', 'POST', fd).then(() => {
                        this.saving = false; this.resetForm(); this.getCases();
                    });
                    return;
                }
                this.saving = false; this.resetForm(); this.getCases();
            });
        },
        removeCase(id) {
            if (!confirm('Удалить кейс?')) return;
            this.datasend('admin/cases/' + id, 'DELETE').then(() => this.getCases());
        },
    },
};
</script>
