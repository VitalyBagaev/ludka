<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title">Пул скинов</h1>
        <p class="ludo-page-sub">Управление предметами в дропе</p>

        <div class="ludo-card p-4 mb-4">
            <div class="ludo-section-label mb-3">{{ editId ? 'Редактировать' : 'Добавить' }} скин</div>
            <div class="row g-2">
                <div class="col-md-3"><input v-model="form.name" class="form-control ludo-form-control" placeholder="Название" /></div>
                <div class="col-md-2"><input v-model="form.weapon" class="form-control ludo-form-control" placeholder="Оружие" /></div>
                <div class="col-md-2">
                    <select v-model="form.rarity" class="form-select ludo-form-control">
                        <option value="consumer">Ширпотреб</option>
                        <option value="industrial">Промышленное качество</option>
                        <option value="mil-spec">Армейское качество</option>
                        <option value="restricted">Запрещённое</option>
                        <option value="classified">Засекреченное</option>
                        <option value="covert">Тайное</option>
                        <option value="gold">★ Экзотическое</option>
                    </select>
                </div>
                <div class="col-md-2"><input v-model.number="form.price" type="number" class="form-control ludo-form-control" placeholder="Цена" /></div>
                <div class="col-md-3">
                    <label class="btn btn-sm btn-ludo-outline w-100 mb-0">
                        {{ imageFile ? 'Файл выбран' : 'Картинка (необяз.)' }}
                        <input type="file" accept="image/*" class="d-none" @change="onImagePick" />
                    </label>
                </div>
            </div>
            <div v-if="previewImage" class="mt-3 text-center">
                <img :src="previewImage" style="height: 72px" class="mb-2" @error="imgError($event, 'skin')" />
            </div>
            <div v-if="message" class="ludo-alert ludo-alert-danger mt-3 mb-0">{{ message }}</div>
            <button class="btn btn-ludo-primary btn-sm mt-3" :disabled="saving" @click="save">
                {{ saving ? '...' : editId ? 'Сохранить' : 'Добавить' }}
            </button>
        </div>

        <div class="row g-3">
            <div v-for="s in skins" :key="s.id" class="col-md-3">
                <div class="ludo-card p-3 text-center">
                    <img :src="skinImg(s.image)" style="height: 60px" class="mb-2" @error="imgError($event, 'skin')" />
                    <div class="small">{{ s.weapon }} | {{ s.name }}</div>
                    <div class="ludo-case-price mb-2">{{ s.price }} ₽</div>
                    <button class="btn btn-ludo-outline btn-sm me-1" @click="editSkin(s)">✎</button>
                    <button class="btn btn-sm btn-outline-danger" @click="removeSkin(s.id)">×</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
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
            form: { name: '', weapon: '', rarity: 'consumer', price: 100 },
        };
    },
    mounted() {
        this.getSkins();
    },
    methods: {
        getSkins() {
            this.datasend('admin/skins').then((result) => {
                if (result) {
                    this.skins = result;
                }
            });
        },
        onImagePick(event) {
            const file = event.target.files && event.target.files[0];
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
                if (!result) {
                    this.message = 'Ошибка сохранения';
                    return;
                }
                if (result.message && !result.id) {
                    this.message = result.message;
                    return;
                }
                if (result.errors) {
                    this.message = Object.values(result.errors).flat().join(' ');
                    return;
                }
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
        },
        removeSkin(id) {
            if (!confirm('Удалить?')) {
                return;
            }
            this.datasend('admin/skins/' + id, 'DELETE').then(() => {
                this.getSkins();
            });
        },
    },
};
</script>
