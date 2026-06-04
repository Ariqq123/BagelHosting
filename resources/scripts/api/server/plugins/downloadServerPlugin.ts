import http from '@/api/http';

export default async (uuid: string, plugin: number): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/plugins/${plugin}/download`);
};
