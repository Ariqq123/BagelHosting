import React from 'react';

/* blueprint/import */import FreeserversPageRoute from '@blueprint/extensions/freeservers/FreeServersPageWrapper';

interface RouteDefinition {
  path: string;
  name: string | undefined;
  component: React.ComponentType;
  exact?: boolean;
  adminOnly: boolean | false;
  identifier: string;
}
interface ServerRouteDefinition extends RouteDefinition {
  permission: string | string[] | null;
}
interface Routes {
  account: RouteDefinition[];
  server: ServerRouteDefinition[];
}

export default {
  account: [
    /* routes/account */{ path: '/freeservers', name: 'Free Servers', component: FreeserversPageRoute, adminOnly: false, identifier: 'freeservers' },
  ],
  server: [
    /* routes/server */
  ],
} as Routes;
