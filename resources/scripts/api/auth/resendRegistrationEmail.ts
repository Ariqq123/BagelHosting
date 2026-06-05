import http from '@/api/http';

export default (email: string, recaptchaData?: string): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/register/resend', {
            email,
            'g-recaptcha-response': recaptchaData,
        })
            .then((response) => resolve(response.data.message || ''))
            .catch(reject);
    });
};
