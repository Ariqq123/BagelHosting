import React, { useEffect } from 'react';
import MessageBox from '@/components/MessageBox';
import Portal from '@/components/elements/Portal';
import { useStoreState, useStoreActions } from 'easy-peasy';
import tw, { css } from 'twin.macro';

type Props = Readonly<{
    byKey?: string;
    className?: string;
}>;

const FlashMessageRender = ({ byKey, className }: Props) => {
    const flashes = useStoreState((state) =>
        state.flashes.items.filter((flash) => (byKey ? flash.key === byKey : true))
    );

    const clearFlashes = useStoreActions((actions) => actions.flashes.clearFlashes);

    useEffect(() => {
        if (flashes.length > 0) {
            const timeoutId = setTimeout(() => {
                clearFlashes(byKey);
            }, 6000);
            return () => clearTimeout(timeoutId);
        }
        return () => {};
    }, [flashes, clearFlashes, byKey]);

    return flashes.length ? (
        <div>
            {flashes.map((flash, index) => (
                <React.Fragment key={flash.id || flash.type + flash.message}>
                    <Portal>
                        <MessageBox type={flash.type} title={flash.title}>
                            {flash.message}
                        </MessageBox>
                    </Portal>
                </React.Fragment>
            ))}
        </div>
    ) : null;
};

export default FlashMessageRender;