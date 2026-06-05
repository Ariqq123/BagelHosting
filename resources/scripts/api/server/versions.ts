import http from '@/api/http';

export interface ServerVersionSoftware {
    id: number;
    name: string;
    description: string;
}

export interface ServerVersionsResponse {
    software: ServerVersionSoftware[];
    current: {
        egg_id: number | null;
        name: string | null;
        version: string;
    };
}

export const getServerVersions = async (uuid: string): Promise<ServerVersionsResponse> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/versions`);

    return data;
};

export const installServerVersion = async (uuid: string, eggId: number, version: string): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/versions`, {
        egg_id: eggId,
        version,
    });
};
