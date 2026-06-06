import React from 'react';
import Spinner from '@/components/elements/Spinner';
import Console from '@/components/server/console/Console';

const FullConsoleContainer = () => {
    return (
        <Spinner.Suspense>
            <Console fullConsole />
        </Spinner.Suspense>
    );
};

export default FullConsoleContainer;
