import http from '@/api/http';

export default (uuid: string, subdomain: number): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/servers/${uuid}/subdomains/${subdomain}`)
            .then(() => resolve())
            .catch(reject);
    });
};
