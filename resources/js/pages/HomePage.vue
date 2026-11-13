<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1>Открывай кейсы на <em>Лудомании</em></h1>
                    <p>CS2 скины, битвы, апгрейд и контракты — всё в одном месте.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                    <span class="ludo-stat-pill me-2">Кейсов: <strong>{{ cases.length }}</strong></span>
                    <span class="ludo-stat-pill">+30% к первому депозиту</span>
                </div>
            </div>
        </div>

        <div class="ludo-filter-tabs mb-4">
            <button
                v-for="tab in filterTabs"
                :key="tab.key"
                class="ludo-filter-tab"
                :class="{ active: category === tab.key }"
                @click="setCategory(tab.key)"
            >
                {{ tab.label }}
            </button>
        </div>

        <template v-for="section in visibleSections" :key="section.key">
            <div class="ludo-home-section" :id="'cases-' + section.key">
                <div class="ludo-section-label mb-3">{{ section.label }}</div>
                <div class="row g-3 mb-4">
                    <div v-for="item in section.cases" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="ludo-case-card" :class="{ 'ludo-card-featured': item.featured }" @click="openCase(item.id)">
                            <div class="ludo-case-img-wrap">
                                <img :src="caseImg(item.image)" :alt="item.name" @error="imgError($event, 'case')" />
                            </div>
                            <div class="ludo-case-card-body">
                                <div class="ludo-case-name">{{ item.name }}</div>
                                <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                                    <span class="ludo-case-price mb-0">{{ item.price }} ₽</span>
                                    <span v-if="item.featured" class="ludo-case-badge">Хит</span>
                                </div>
                                <button class="btn btn-ludo-primary btn-sm ludo-case-card-btn">Открыть</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { categoryLabels, categoryOrder } from '../helpers/game.js';

export default {
    name: 'HomePage',
    props: ['changePage', 'datasend', 'user', 'userInfo', 'getUser', 'caseImg', 'imgError'],
    data() {
        return {
            cases: [],
            category: 'all',
        };
    },
    mounted() {
        this.getCases();
    },
    methods: {
        getCases() {
            this.datasend('cases').then((result) => {
                if (result) {
                    this.cases = result;
                }
            });
        },
        setCategory(key) {
            this.category = key;
            if (key !== 'all') {
                this.$nextTick(() => {
                    document.getElementById('cases-' + key)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        },
        openCase(id) {
            this.changePage('CasePage', id);
        },
        sortByPriceDesc(list) {
            return [...list].sort((left, right) => right.price - left.price || left.name.localeCompare(right.name));
        },
    },
    computed: {
        filterTabs() {
            return [
                { key: 'all', label: categoryLabels.all },
                ...categoryOrder.map((key) => ({ key, label: categoryLabels[key] })),
            ];
        },
        categorySections() {
            return categoryOrder
                .map((key) => ({
                    key,
                    label: categoryLabels[key],
                    cases: this.sortByPriceDesc(this.cases.filter((item) => item.category === key)),
                }))
                .filter((section) => section.cases.length > 0);
        },
        visibleSections() {
            if (this.category === 'all') {
                return this.categorySections;
            }

            return this.categorySections.filter((section) => section.key === this.category);
        },
    },
};
</script>
