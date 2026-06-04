import { languages, normalizeLanguage } from '@/components/dashboard/forms/languages';

describe('@/components/dashboard/forms/languages.ts', function () {
    it('includes every installed Arix translation locale', function () {
        expect(languages.map((language) => language.key)).toEqual([
            'en',
            'cs',
            'da',
            'de',
            'es',
            'fr',
            'hi',
            'hi-EN',
            'id',
            'it',
            'ja',
            'ko',
            'lt',
            'nl',
            'pa',
            'pt',
            'pt-BR',
            'ro',
            'ru',
            'sv',
            'tr',
            'uk',
            'ur',
        ]);
    });
});

describe('normalizeLanguage()', function () {
    it('keeps exact supported locale matches', function () {
        expect(normalizeLanguage('pt-BR')).toBe('pt-BR');
        expect(normalizeLanguage('pt-br')).toBe('pt-BR');
    });

    it('falls back from unsupported region variants to supported base locales', function () {
        expect(normalizeLanguage('de-DE')).toBe('de');
        expect(normalizeLanguage('fr-CA')).toBe('fr');
    });

    it('falls back to English for unsupported or empty locales', function () {
        expect(normalizeLanguage('en-US')).toBe('en');
        expect(normalizeLanguage('zh-CN')).toBe('en');
        expect(normalizeLanguage(undefined)).toBe('en');
    });
});
