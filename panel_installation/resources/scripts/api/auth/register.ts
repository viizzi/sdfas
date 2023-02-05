import http from '@/api/http';

export interface RegisterResponse {
    complete: boolean;
    message: string;
}

export interface RegisterData {
    email: string;
    username: string;
    first_name: string;
    last_name: string;
    password: string;
    recaptchaData?: string | null;
}

export default ({ email, username, first_name, last_name, password, recaptchaData }: RegisterData): Promise<RegisterResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/register', {
            email: email,
            username: username,
            first_name: first_name,
            last_name: last_name,
            password: password,
            'g-recaptcha-response': recaptchaData,
        })
            .then((response) => {
                resolve({
                    complete: response.data.complete,
                    message: response.data.message,
                });
            })
            .catch(reject);
    });
};
