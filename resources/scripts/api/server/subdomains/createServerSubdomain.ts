import http from '@/api/http';
import { rawDataToServerSubdomain, ServerSubdomain } from '@/api/server/subdomains/getServerSubdomains';

interface Values {
    name: string;
    domainId: number;
    type: string;
}

export default (uuid: string, values: Values): Promise<ServerSubdomain> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/subdomains`, {
            name: values.name,
            domain_id: values.domainId,
            type: values.type,
        })
            .then(({ data }) => resolve(rawDataToServerSubdomain(data.attributes)))
            .catch(reject);
    });
};
