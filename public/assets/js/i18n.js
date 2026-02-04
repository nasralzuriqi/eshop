let translations = {};
let currentLang = 'en';

async function setLanguage(lang) {
    currentLang = lang;
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';

    try {
        const response = await fetch(`assets/locales/${lang}.json`);
        translations = await response.json();
        translatePage();
        // Save language preference
        localStorage.setItem('language', lang);
    } catch (error) {
        console.error(`Could not load language file: ${lang}.json`, error);
    }
}

function translateNode(node) {
    if (!node.querySelectorAll) return;

    node.querySelectorAll('[data-i18n-key]').forEach(element => {
        const key = element.getAttribute('data-i18n-key');
        element.textContent = getTranslation(key);
    });

    node.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
        const key = element.getAttribute('data-i18n-placeholder');
        element.placeholder = getTranslation(key);
    });
}

function translatePage() {
    translateNode(document.body);
}

function getTranslation(key) {
    return translations[key] || key;
}

// Initialize the language
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('language') || 'ar';
    setLanguage(savedLang);

    // Observe the body for changes and translate new nodes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    translateNode(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
