import http, { FractalResponseList } from '@/api/http';

export interface ServerPlugin {
    id: number;
    name: string;
    description: string | null;
    filename: string;
    iconUrl: string | null;
}

const rawDataToServerPlugin = ({ attributes }: any): ServerPlugin => ({
    id: attributes.id,
    name: attributes.name,
    description: attributes.description,
    filename: attributes.filename,
    iconUrl: attributes.icon_url,
});

export default async (uuid: string): Promise<ServerPlugin[]> => {
    const { data } = await http.get<FractalResponseList>(`/api/client/servers/${uuid}/plugins`);

    return (data.data || []).map(rawDataToServerPlugin);
};
