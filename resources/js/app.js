import './bootstrap';
import 'bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const contributionInput = document.querySelector('#monthly-contribution');
const yearsInput = document.querySelector('#investment-years');
const rateInput = document.querySelector('#assumed-rate');
const contributionTotal = document.querySelector('#contribution-total');
const scenarioTotal = document.querySelector('#scenario-total');
const chartCanvas = document.querySelector('#growth-chart');

if (contributionInput && yearsInput && rateInput && contributionTotal && scenarioTotal && chartCanvas) {
    const money = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    });
    const chart = new Chart(chartCanvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'Contributions only', data: [], borderColor: '#758b7c', borderWidth: 2, pointRadius: 0, fill: false, tension: .28 },
                { label: 'User-selected hypothetical rate', data: [], borderColor: '#39a972', backgroundColor: 'rgba(57,169,114,.12)', borderWidth: 2.5, pointRadius: 0, fill: true, tension: .28 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'bottom', labels: { color: '#b4c2b8', usePointStyle: true, boxWidth: 7, padding: 16, font: { size: 10 } } },
                tooltip: { callbacks: { label: item => `${item.dataset.label}: ${money.format(item.parsed.y)}` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#92a398', maxTicksLimit: 6, font: { size: 9 } }, border: { display: false } },
                y: { grid: { color: 'rgba(194,216,198,.1)' }, ticks: { color: '#92a398', maxTicksLimit: 4, callback: value => money.format(value), font: { size: 9 } }, border: { display: false } },
            },
        },
    });

    const updateScenario = () => {
        const monthlyInput = Number(contributionInput.value);
        const years = Number(yearsInput.value);
        const annualRateInput = Number(rateInput.value);
        const validInputs = contributionInput.value !== ''
            && yearsInput.value !== ''
            && rateInput.value !== ''
            && Number.isFinite(monthlyInput)
            && monthlyInput >= 25
            && monthlyInput <= 100000
            && Number.isFinite(years)
            && Number.isFinite(annualRateInput)
            && annualRateInput >= 0
            && annualRateInput <= 20;

        if (!validInputs) {
            contributionTotal.textContent = 'Not available';
            scenarioTotal.textContent = 'Not available';
            chart.data.labels = [];
            chart.data.datasets.forEach(dataset => { dataset.data = []; });
            chart.update();
            return;
        }

        const monthly = monthlyInput;
        const annualRate = annualRateInput / 100;
        const months = years * 12;
        const monthlyRate = annualRate / 12;
        const labels = [];
        const contributions = [];
        const hypotheticalValues = [];

        for (let month = 0; month <= months; month += 1) {
            if (month % 12 !== 0 && month !== months) continue;
            const totalContributions = monthly * month;
            const scenarioValue = monthlyRate === 0
                ? totalContributions
                : monthly * ((Math.pow(1 + monthlyRate, month) - 1) / monthlyRate);
            labels.push(month === 0 ? 'Today' : `${month / 12} yr${month / 12 === 1 ? '' : 's'}`);
            contributions.push(Math.round(totalContributions));
            hypotheticalValues.push(Math.round(scenarioValue));
        }

        const futureValue = monthlyRate === 0
            ? monthly * months
            : monthly * ((Math.pow(1 + monthlyRate, months) - 1) / monthlyRate);
        contributionTotal.textContent = money.format(monthly * months);
        scenarioTotal.textContent = money.format(futureValue);
        chart.data.labels = labels;
        chart.data.datasets[0].data = contributions;
        chart.data.datasets[1].data = hypotheticalValues;
        chart.update();
    };

    [contributionInput, yearsInput, rateInput].forEach(input => input.addEventListener('input', updateScenario));
    updateScenario();
}
const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
const revealTargets = document.querySelectorAll('.product-card, .catalog-card, .portal-card, .portal-panel, .detail-main, .detail-aside, .contact-card, .page-hero > div, .portal-header > div, .auth-card, .home-story-chapter, .home-section-heading, .closing-inner');
if (!motionPreference.matches && 'IntersectionObserver' in window && revealTargets.length > 0) {
    revealTargets.forEach((element, index) => {
        element.dataset.reveal = '';
        element.style.setProperty('--reveal-delay', String((index % 4) * 70) + 'ms');
    });
    document.documentElement.classList.add('motion-ready');
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -28px 0px' });
    revealTargets.forEach(element => revealObserver.observe(element));
}
const activityTrendCanvas = document.querySelector('#activity-trend-chart');
if (activityTrendCanvas) {
    const labels = (activityTrendCanvas.dataset.labels || '').split('|');
    const values = (activityTrendCanvas.dataset.values || '').split(',').map(Number);
    new Chart(activityTrendCanvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Posted ledger entries',
                data: values,
                backgroundColor: 'rgba(30,150,99,.72)',
                borderColor: '#168052',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 34,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#718378' }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#718378' }, grid: { color: 'rgba(16,59,42,.08)' }, border: { display: false } },
            },
        },
    });
}
const adminTransactionCanvas = document.querySelector('#admin-transaction-chart');
if (adminTransactionCanvas) {
    const labels = (adminTransactionCanvas.dataset.labels || '').split('|');
    const values = (adminTransactionCanvas.dataset.values || '').split(',').map(Number);
    new Chart(adminTransactionCanvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Posted transactions',
                data: values,
                backgroundColor: 'rgba(30,150,99,.72)',
                borderColor: '#168052',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 34,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#718378' }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#718378' }, grid: { color: 'rgba(16,59,42,.08)' }, border: { display: false } },
            },
        },
    });
}
const navToggle = document.querySelector('.site-nav-toggle');
const navMenu = document.querySelector('#site-nav-menu');
if (navToggle && navMenu) {
    const closeMenu = () => {
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-label', 'Open navigation');
        navMenu.classList.remove('is-open');
    };
    navToggle.addEventListener('click', () => {
        const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', String(!isOpen));
        navToggle.setAttribute('aria-label', isOpen ? 'Open navigation' : 'Close navigation');
        navMenu.classList.toggle('is-open', !isOpen);
    });
    navMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
    window.addEventListener('resize', () => { if (window.innerWidth > 820) closeMenu(); });
}

const adminShell = document.querySelector('.admin-shell');
if (adminShell) {
    const sidebar = document.querySelector('#admin-sidebar');
    const mobileToggle = document.querySelector('[data-admin-mobile-toggle]');
    const collapseToggle = document.querySelector('[data-admin-collapse-toggle]');
    const backdrop = document.querySelector('[data-admin-backdrop]');
    const storageKey = 'commonwealth.adminSidebarCollapsed';

    const closeMobileSidebar = () => {
        adminShell.classList.remove('admin-shell--sidebar-open');
        mobileToggle?.setAttribute('aria-expanded', 'false');
        mobileToggle?.setAttribute('aria-label', 'Open administration navigation');
    };

    const setCollapsed = collapsed => {
        adminShell.classList.toggle('admin-shell--collapsed', collapsed);
        collapseToggle?.setAttribute('aria-expanded', String(!collapsed));
        collapseToggle?.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        collapseToggle?.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        try {
            window.localStorage.setItem(storageKey, String(collapsed));
        } catch {
            // Sidebar preference is optional when browser storage is unavailable.
        }
    };

    if (collapseToggle) {
        try {
            setCollapsed(window.localStorage.getItem(storageKey) === 'true');
        } catch {
            setCollapsed(false);
        }
        collapseToggle.addEventListener('click', () => {
            setCollapsed(!adminShell.classList.contains('admin-shell--collapsed'));
        });
    }

    mobileToggle?.addEventListener('click', () => {
        const isOpen = adminShell.classList.toggle('admin-shell--sidebar-open');
        mobileToggle.setAttribute('aria-expanded', String(isOpen));
        mobileToggle.setAttribute('aria-label', isOpen ? 'Close administration navigation' : 'Open administration navigation');
    });
    backdrop?.addEventListener('click', closeMobileSidebar);
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileSidebar));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeMobileSidebar();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 820) closeMobileSidebar();
    });
}


const adminChartCanvases = document.querySelectorAll('[data-admin-chart]');
adminChartCanvases.forEach(canvas => {
    let datasets;
    try {
        datasets = JSON.parse(window.atob(canvas.dataset.series || 'W10='));
    } catch {
        datasets = [];
    }
    const labels = (canvas.dataset.labels || '').split('|');
    const palette = ['#3b8b59', '#c0a15e', '#809894', '#7394a1'];
    const chartDatasets = datasets.map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: palette[index % palette.length],
        backgroundColor: canvas.dataset.chartKind === 'line' ? 'transparent' : palette[index % palette.length] + 'bb',
        borderWidth: 2,
        pointRadius: canvas.dataset.chartKind === 'line' ? 2 : 0,
        fill: false,
        tension: .25,
        borderRadius: canvas.dataset.chartKind === 'bar' ? 4 : 0,
        maxBarThickness: 32,
    }));
    new Chart(canvas.getContext('2d'), {
        type: canvas.dataset.chartKind || 'bar',
        data: { labels, datasets: chartDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'bottom', labels: { color: '#627469', usePointStyle: true, boxWidth: 7, padding: 16, font: { size: 9 } } },
                tooltip: { enabled: true },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#728176', maxTicksLimit: 6, font: { size: 9 } }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#728176', maxTicksLimit: 5, font: { size: 9 } }, grid: { color: 'rgba(39,77,49,.1)' }, border: { display: false } },
            },
        },
    });
});


const storyChapters = document.querySelectorAll('[data-story-chapter]');
const storyVisuals = document.querySelectorAll('[data-story-visual]');
if (storyChapters.length && storyVisuals.length && 'IntersectionObserver' in window) {
    const activateStoryScene = key => {
        storyVisuals.forEach(visual => {
            const isActive = visual.dataset.storyVisual === key;
            visual.classList.toggle('is-active', isActive);
            visual.setAttribute('aria-hidden', String(!isActive));
        });
    };
    const storyObserver = new IntersectionObserver(entries => {
        const activeChapter = entries
            .filter(entry => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (activeChapter) activateStoryScene(activeChapter.target.dataset.storyChapter);
    }, { rootMargin: '-36% 0px -36% 0px', threshold: [0, 0.2, 0.45, 0.7] });
    storyChapters.forEach(chapter => storyObserver.observe(chapter));
}
