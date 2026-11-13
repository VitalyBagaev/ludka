<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title">Кейсы</h1>
        <p class="ludo-page-sub">Создание и редактирование кейсов, категорий и hot-меток</p>

        <div class="ludo-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="ludo-section-label mb-0">{{ editId ? 'Редактировать' : 'Новый' }} кейс</div>
                <button v-if="editId" class="btn btn-sm btn-ludo-outline" @click="resetForm">Отмена</button>
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <input v-model="form.name" class="form-control ludo-form-control" placeholder="Название" />
                </div>
                <div class="col-md-2">
                    <input v-model.number="form.price" type="number" min="1" class="form-control ludo-form-control" placeholder="Цена ₽" />
                </div>
                <div class="col-md-3">
                    <select v-model="form.category" class="form-select ludo-form-control">
                        <option v-for="(label, key) in categoryLabels" :key="key" v-show="key !== 'all'" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="small mb-0">
                        <input v-model="form.featured" type="checkbox" class="form-check-input me-1" />
                        Hot
                    </label>
                </div>
                <div class="col-md-2">
                    <label class="btn btn-sm btn-ludo-outline w-100 mb-0">
                        {{ imageFile ? 'Обложка ✓' : 'Обложка' }}
                        <input type="file" accept="image/*" class="d-none" @change="onImagePick" />
                    </label>
                </div>
            </div>
            <div v-if="previewImage" class="mt-3 text-center">
                <img :src="previewImage" style="height: 80px" @error="imgError($event, 'case')" />
            </div>
            <div v-if="form.price >= 5000" class="small mt-2" :class="cheapTierCount >= 10 ? 'text-success' : 'text-warning'">
                Дешёвых скинов (50%–80%): {{ cheapTierCount }} / 10
                <span v-if="cheapTierCount < 10"> — добавьте ещё {{ 10 - cheapTierCount }}</span>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-2 ms-auto">
                    <button class="btn btn-ludo-primary btn-sm w-100" :disabled="saving" @click="save">
                        {{ saving ? '...' : editId ? 'Сохранить' : 'Создать' }}
                    </button>
                </div>
            </div>

            <div v-if="message" class="ludo-alert ludo-alert-danger mt-3 mb-0">{{ message }}</div>

            <div class="mt-4">
                <div class="ludo-section-label mb-2">Скины в кейсе ({{ selectedSkins.length }})</div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input
                            v-model="skinSearch"
                            class="form-control ludo-form-control"
                            placeholder="Поиск скина по названию или оружию..."
                        />
                    </div>
                </div>

                <div v-if="selectedSkins.length" class="mb-3">
                    <div
                        v-for="item in selectedSkins"
                        :key="'sel-' + item.skin_id"
                        class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary-subtle"
                    >
                        <div class="small">
                            <span class="text-secondary">{{ item.weapon }}</span>
                            <span class="mx-1">|</span>
                            <span class="fw-semibold">{{ item.name }}</span>
                            <span class="ludo-case-price ms-2">{{ item.price }} ₽</span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" @click="removeSkin(item.skin_id)">×</button>
                    </div>
                </div>
                <div v-else class="small text-secondary mb-3">Скины не выбраны — добавьте из списка ниже</div>

                <div class="ludo-table-wrap" style="max-height: 280px; overflow-y: auto">
                    <div class="table-responsive">
                        <table class="table ludo-table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Скин</th>
                                    <th>Цена</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="skin in filteredSkins" :key="skin.id">
                                    <td class="small">{{ skin.weapon }} | {{ skin.name }}</td>
                                    <td class="ludo-case-price small">{{ skin.price }} ₽</td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-sm btn-ludo-outline"
                                            :disabled="isSkinSelected(skin.id)"
                                            @click="addSkin(skin)"
                                        >
                                            {{ isSkinSelected(skin.id) ? 'Добавлен' : '+' }}
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
            </div>
        </div>

        <div class="ludo-table-wrap">
            <div class="table-responsive">
                <table class="table ludo-table table-striped">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Hot</th>
                            <th>Скинов</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in cases" :key="c.id">
                            <td>{{ c.name }}</td>
                            <td>{{ categoryLabels[c.category] || c.category }}</td>
                            <td class="ludo-case-price">{{ c.price }} ₽</td>
                            <td>{{ c.featured ? '🔥' : '—' }}</td>
                            <td>{{ c.skins_count ?? 0 }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-ludo-outline me-1" @click="editCase(c)">✎</button>
                                <button class="btn btn-sm btn-outline-danger" @click="removeCase(c.id)">×</button>
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
            editId: null,
            saving: false,
            message: null,
            imageFile: null,
            previewImage: null,
            categoryLabels,
            form: {
                name: '',
                price: 100,
                category: 'serial',
                featured: false,
            },
        };
    },
    computed: {
        cheapTierCount() {
            if (this.form.price < 5000) {
                return 0;
            }
            const min = Math.max(500, Math.floor(this.form.price * 0.5));
            const max = Math.floor(this.form.price * 0.8);
            return this.selectedSkins.filter((item) => item.price >= min && item.price <= max).length;
        },
        filteredSkins() {
            const q = this.skinSearch.trim().toLowerCase();
            if (!q) {
                return this.allSkins.slice(0, 80);
            }
            return this.allSkins
                .filter((skin) => {
                    return (
                        skin.name.toLowerCase().includes(q) ||
                        skin.weapon.toLowerCase().includes(q) ||
                        String(skin.price).includes(q)
                    );
                })
                .slice(0, 80);
        },
    },
    mounted() {
        this.getCases();
        this.getSkins();
    },
    methods: {
        getCases() {
            this.datasend('admin/cases').then((result) => {
                if (result) {
                    this.cases = result;
                }
            });
        },
        getSkins() {
            this.datasend('admin/skins').then((result) => {
                if (result) {
                    this.allSkins = result;
                }
            });
        },
        isSkinSelected(id) {
            return this.selectedSkins.some((item) => item.skin_id === id);
        },
        addSkin(skin) {
            if (this.isSkinSelected(skin.id)) {
                return;
            }
            this.selectedSkins.push({
                skin_id: skin.id,
                drop_weight: 100,
                name: skin.name,
                weapon: skin.weapon,
                price: skin.price,
            });
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
            const file = event.target.files && event.target.files[0];
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
                this.form = {
                    name: result.name,
                    price: result.price,
                    category: result.category,
                    featured: !!result.featured,
                };
                this.imageFile = null;
                this.previewImage = this.caseImg ? this.caseImg(result.image) : result.image;
                this.selectedSkins = (result.skins || []).map((skin) => ({
                    skin_id: skin.skin_id || skin.id,
                    drop_weight: skin.drop_weight || 100,
                    name: skin.name,
                    weapon: skin.weapon,
                    price: skin.price,
                }));
            });
        },
        save() {
            if (!this.form.name.trim()) {
                this.message = 'Укажите название кейса';
                return;
            }
            this.saving = true;
            this.message = null;
            const route = this.editId ? 'admin/cases/' + this.editId : 'admin/cases';
            const payload = {
                ...this.form,
                skins: this.selectedSkins.map((item) => ({
                    skin_id: item.skin_id,
                    drop_weight: item.drop_weight,
                })),
            };

            this.datasendJson(route, 'POST', payload).then((result) => {
                if (!result || result.message || result.errors) {
                    this.saving = false;
                    if (!result) {
                        this.message = 'Ошибка сохранения';
                    } else if (result.message) {
                        this.message = result.message;
                    } else if (result.errors) {
                        this.message = Object.values(result.errors).flat().join(' ');
                    }
                    return;
                }

                const caseId = result.id || this.editId;
                if (this.imageFile && caseId) {
                    const fd = new FormData();
                    fd.append('image', this.imageFile);
                    this.datasend('admin/cases/' + caseId + '/image', 'POST', fd).then(() => {
                        this.saving = false;
                        this.resetForm();
                        this.getCases();
                    });
                    return;
                }

                this.saving = false;
                this.resetForm();
                this.getCases();
            });
        },
        removeCase(id) {
            if (!confirm('Удалить кейс?')) return;
            this.datasend('admin/cases/' + id, 'DELETE').then(() => this.getCases());
        },
    },
};
</script>
