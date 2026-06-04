import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend, { BackendOptions } from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';
import LanguageDetector from 'i18next-browser-languagedetector';
import { normalizeLanguage } from '@/components/dashboard/forms/languages';

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = module.hot ? Date.now().toString(16) : process.env.WEBPACK_BUILD_HASH;

const browserLanguageDetector = {
    name: 'browserLanguage',
    lookup: () => normalizeLanguage(typeof window === 'undefined' ? undefined : window.navigator.language),
};

const languageDetector = new LanguageDetector();
languageDetector.addDetector(browserLanguageDetector);

i18n.use(I18NextMultiloadBackendAdapter)
    .use(initReactI18next)
    .use(languageDetector)
    .init({
        detection: {
            order: ['localStorage', 'browserLanguage'],
            caches: ['localStorage'],
            lookupLocalStorage: 'i18nextLng',
        },
        debug: process.env.DEBUG === 'true',
        fallbackLng: 'en',
        keySeparator: '.',
        backend: {
            backend: I18NextHttpBackend,
            backendOption: {
                loadPath: (languages: string[], namespaces: string[]) => {
                    return `/locales/locale.json?locale=${languages.join(',')}&namespace=${namespaces.join(',')}`;
                },
                queryStringParams: { hash },
                allowMultiLoading: true,
            } as BackendOptions,
        } as Record<string, any>,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    })
    .then(() => {
        console.log('i18next initialized successfully');
    })
    .catch((error) => {
        console.error('Error initializing i18next:', error);
    });

export default i18n;
