import React, { lazy } from 'react';
import DashboardContainer from '@/components/server/dashboard/DashboardContainer';
import ServerConsole from '@/components/server/console/ServerConsoleContainer';
import FullConsoleContainer from '@/components/server/console/FullConsoleContainer';
import DatabasesContainer from '@/components/server/databases/DatabasesContainer';
import ScheduleContainer from '@/components/server/schedules/ScheduleContainer';
import UsersContainer from '@/components/server/users/UsersContainer';
import BackupContainer from '@/components/server/backups/BackupContainer';
import NetworkContainer from '@/components/server/network/NetworkContainer';
import StartupContainer from '@/components/server/startup/StartupContainer';
import FileManagerContainer from '@/components/server/files/FileManagerContainer';
import PluginsContainer from '@/components/server/plugins/PluginsContainer';
import SettingsContainer from '@/components/server/settings/SettingsContainer';
import AccountOverviewContainer from '@/components/dashboard/AccountOverviewContainer';
import ActivityLogContainer from '@/components/dashboard/activity/ActivityLogContainer';
import ServerActivityLogContainer from '@/components/server/ServerActivityLogContainer';
import {
    UserIcon,
    EyeIcon,
    ViewGridIcon,
    TerminalIcon,
    FolderOpenIcon,
    DatabaseIcon,
    CalendarIcon,
    UserGroupIcon,
    ArchiveIcon,
    GlobeIcon,
    AdjustmentsIcon,
    CogIcon,
    PuzzleIcon,
} from '@heroicons/react/outline';

const FileEditContainer = lazy(() => import('@/components/server/files/FileEditContainer'));
const ScheduleEditContainer = lazy(() => import('@/components/server/schedules/ScheduleEditContainer'));

/*
 * Arix Theme keeps server navigation data-driven here; add addon tabs by
 * importing the component and icon, then adding a route definition below.
 */

interface RouteDefinition {
    path: string;
    // If undefined is passed this route is still rendered into the router itself
    // but no navigation link is displayed in the sub-navigation menu.
    name: string | undefined;
    component: React.ComponentType;
    icon?: React.ComponentType;
    exact?: boolean;
}

interface ServerRouteDefinition extends RouteDefinition {
    permission: string | string[] | null;
    nestId?: number;
    eggId?: number;
    nestIds?: number[];
    eggIds?: number[];
}

interface Routes {
    // All of the routes available under "/account"
    account: RouteDefinition[];
    // All of the routes available under "/server/:id"
    server: {
        general: ServerRouteDefinition[];
        management: ServerRouteDefinition[];
        configuration: ServerRouteDefinition[];
    };
}

export default {
    account: [
        {
            path: '/',
            name: 'account',
            icon: UserIcon,
            component: AccountOverviewContainer,
            exact: true,
        },
        {
            path: '/activity',
            name: 'account-activity',
            icon: EyeIcon,
            component: ActivityLogContainer,
        },
    ],
    server: {
        general: [
            {
                path: '/',
                permission: null,
                name: 'dashboard',
                icon: ViewGridIcon,
                component: DashboardContainer,
                exact: true,
            },
            {
                path: '/console',
                permission: null,
                name: 'console',
                icon: TerminalIcon,
                component: ServerConsole,
                exact: true,
            },
            {
                path: '/console/popup',
                permission: null,
                icon: TerminalIcon,
                name: undefined,
                component: FullConsoleContainer,
            },
            {
                path: '/settings',
                permission: ['settings.*', 'file.sftp'],
                name: 'settings',
                icon: CogIcon,
                component: SettingsContainer,
            },
            {
                path: '/activity',
                permission: 'activity.*',
                name: 'activity',
                icon: EyeIcon,
                component: ServerActivityLogContainer,
            },
        ],
        management: [
            {
                path: '/files',
                permission: 'file.*',
                name: 'files',
                icon: FolderOpenIcon,
                component: FileManagerContainer,
            },
            {
                path: '/files/:action(edit|new)',
                permission: 'file.*',
                name: undefined,
                component: FileEditContainer,
            },
            {
                path: '/databases',
                permission: 'database.*',
                name: 'databases',
                icon: DatabaseIcon,
                component: DatabasesContainer,
            },
            {
                path: '/backups',
                permission: 'backup.*',
                name: 'backups',
                icon: ArchiveIcon,
                component: BackupContainer,
            },
            {
                path: '/network',
                permission: 'allocation.*',
                name: 'network',
                icon: GlobeIcon,
                component: NetworkContainer,
            },
            {
                path: '/plugins',
                permission: 'file.create',
                name: 'plugins',
                icon: PuzzleIcon,
                component: PluginsContainer,
            },
        ],
        configuration: [
            {
                path: '/schedules',
                permission: 'schedule.*',
                name: 'schedules',
                icon: CalendarIcon,
                component: ScheduleContainer,
            },
            {
                path: '/schedules/:id',
                permission: 'schedule.*',
                name: undefined,
                component: ScheduleEditContainer,
            },
            {
                path: '/users',
                permission: 'user.*',
                name: 'users',
                icon: UserGroupIcon,
                component: UsersContainer,
            },
            {
                path: '/startup',
                permission: 'startup.*',
                name: 'startup',
                icon: AdjustmentsIcon,
                component: StartupContainer,
            },
        ],
    },
} as Routes;
