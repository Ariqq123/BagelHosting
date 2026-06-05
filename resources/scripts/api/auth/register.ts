import http from '@/api/http';

export interface RegisterResponse {
    success: boolean;
    email: string;
    redirectTo: string;
}

interface RegisterData {
    email: string;
    username: string;
    firstName: string;
    lastName: string;
    recaptchaData?: string;
}

export default ({ email, username, firstName, lastName, recaptchaData }: RegisterData): Promise<RegisterResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/register', {
            email,
            username,
            first_name: firstName,
            last_name: lastName,
            'g-recaptcha-response': recaptchaData,
        })
            .then((response) => resolve({
                success: response.data.success,
                email: response.data.email,
                redirectTo: response.data.redirect_to,
            }))
            .catch(reject);
    });
};
