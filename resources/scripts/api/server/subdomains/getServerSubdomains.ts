import http from '@/api/http';

export interface SubdomainDomainOption {
    id: number;
    name: string;
    allowedRecordTypes: string[];
    proxied: boolean;
}

export interface ServerSubdomain {
    id: number;
    name: string;
    fqdn: string;
    type: string;
    content: string;
    proxied: boolean;
    status: string;
    errorMessage: string | null;
    domain: {
        id: number;
        name: string;
    };
    createdAt: string | null;
    updatedAt: string | null;
}

export interface ServerSubdomainResponse {
    items: ServerSubdomain[];
    domains: SubdomainDomainOption[];
    limit: number;
}

export const rawDataToServerSubdomain = (data: any): ServerSubdomain => ({
    id: data.id,
    name: data.name,
    fqdn: data.fqdn,
    type: data.type,
    content: data.content,
    proxied: data.proxied,
    status: data.status,
    errorMessage: data.error_message,
    domain: data.domain,
    createdAt: data.created_at,
    updatedAt: data.updated_at,
});

export const rawDataToSubdomainDomain = (data: any): SubdomainDomainOption => ({
    id: data.id,
    name: data.name,
    allowedRecordTypes: data.allowed_record_types || [],
    proxied: data.proxied,
});

export default (uuid: string): Promise<ServerSubdomainResponse> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/subdomains`)
            .then(({ data }) =>
                resolve({
                    items: (data.data || []).map((item: any) => rawDataToServerSubdomain(item.attributes)),
                    domains: (data.meta?.domains || []).map(rawDataToSubdomainDomain),
                    limit: data.meta?.limit || 0,
                })
            )
            .catch(reject);
    });
};
