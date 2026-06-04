import http from '@/api/http';

export interface InstalledPlugin {
    filename: string;
    tracked: boolean;
    platform: string | null;
    project: string | null;
    version: string | null;
    latestVersion: string | null;
    updateAvailable: boolean;
}

export const getInstalledPlugins = async (uuid: string): Promise<InstalledPlugin[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/plugins/marketplace/installed`);

    return data.data || [];
};

export const updateInstalledPlugin = async (
    uuid: string,
    filename: string
): Promise<{ filename: string; updated: boolean }> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/plugins/marketplace/installed/update`, { filename });

    return data;
};

export const renameInstalledPlugin = async (uuid: string, from: string, to: string): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/plugins/marketplace/installed/rename`, { from, to });
};

export const deleteInstalledPlugin = async (uuid: string, filename: string): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/plugins/marketplace/installed/delete`, { filename });
};
