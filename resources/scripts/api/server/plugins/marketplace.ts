import http from '@/api/http';

export type MarketplacePlatform = 'modrinth' | 'spiget';

export interface MarketplacePlugin {
    platform: MarketplacePlatform;
    id: string;
    slug: string;
    name: string;
    author: string;
    description: string;
    iconUrl: string | null;
    downloads: number;
    stars: number;
    updatedAt: string | null;
    url: string;
    installed: boolean;
    external: boolean;
}

export interface MarketplaceVersion {
    id: string;
    name: string;
    versionNumber: string;
    createdAt: string | null;
    filename: string;
}

export interface SearchParams {
    platform: MarketplacePlatform;
    query: string;
    page: number;
    version?: string;
    loader?: string;
}

export interface SearchResponse {
    data: MarketplacePlugin[];
    meta: { page: number; perPage: number; total: number | null };
}

export const searchMarketplacePlugins = async (uuid: string, params: SearchParams): Promise<SearchResponse> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/plugins/marketplace/search`, { params });

    return data;
};

export const getMarketplaceVersions = async (
    uuid: string,
    platform: MarketplacePlatform,
    project: string,
    version?: string,
    loader?: string
): Promise<MarketplaceVersion[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/plugins/marketplace/${platform}/${project}/versions`, {
        params: { version, loader },
    });

    return data.data || [];
};

export const installMarketplacePlugin = async (
    uuid: string,
    platform: MarketplacePlatform,
    project: string,
    versionId: string | null,
    version?: string,
    loader?: string
): Promise<{ filename: string }> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/plugins/marketplace/${platform}/${project}/install`, {
        version_id: versionId,
        version,
        loader,
    });

    return data;
};
