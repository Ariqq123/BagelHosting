import React, { useState, useEffect } from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { LuPartyPopper, LuMegaphone, LuInfo, LuCircleAlert, LuTriangleAlert, LuCircleCheck, LuCircleX } from "react-icons/lu";
import parser from 'bbcode-to-react';
import styled from 'styled-components/macro';
import tw from 'twin.macro';

const MyAlert = styled.div`
    ${tw`mx-auto w-full flex items-center gap-x-2 max-w-[1200px] px-4 py-3 mt-4 rounded-component text-gray-100 !border-t-0 !border-r-0 !border-b-0`};
    border-left:var(--radiusInput) solid;

    & > svg{
        font-size: 1.2rem;
    }

    &.update{
        ${tw`bg-[#16aaaa33] !border-[#16aaaa]`};
    }
    &.info{
        ${tw`bg-[#0a7fe633] !border-[#0a7fe6]`};
    }
    &.success{
        ${tw`bg-[#0da22c33] !border-[#0da22c]`};
    }
    &.alert{
        ${tw`bg-[#d7c21933] !border-[#d7c219]`};
    }
    &.warning{
        ${tw`bg-[#d7191933] !border-[#d71919]`};
    }

    &.party{
        ${tw`text-white/80 font-medium !border-none`};
        background-color:hsla(248,61%,50%,1);
        background-image:
            radial-gradient(at 50% 100%, hsla(306,61%,51%,1) 0px, transparent 50%),
            radial-gradient(at 93% 30%, hsla(196,74%,78%,1) 0px, transparent 50%);
    }
`;

const Alert = () => {
    const [isOpen, setIsOpen] = useState(true);

    const announcementType = useStoreState((state: ApplicationStore) => state.settings.data!.arix.announcementType);
    const announcementCloseable = useStoreState((state: ApplicationStore) => state.settings.data!.arix.announcementCloseable);
    const announcementMessage = useStoreState((state: ApplicationStore) => state.settings.data!.arix.announcementMessage);

    useEffect(() => {
        const closedTime = localStorage.getItem('closedTime');
        if (closedTime) {
            const timeElapsed = Date.now() - parseInt(closedTime, 10);
            const sixHours = 6 * 60 * 60 * 1000;
            if (timeElapsed < sixHours) {
                setIsOpen(false);
                const timeout = sixHours - timeElapsed;
                setTimeout(() => {
                    setIsOpen(true);
                    localStorage.removeItem('closedTime');
                }, timeout);
            } else {
                localStorage.removeItem('closedTime');
            }
        }
    }, []);

    const handleClose = () => {
        setIsOpen(false);
        localStorage.setItem('closedTime', Date.now().toString());
    };

    return (
        <div className={'px-4'}>
            {announcementType !== 'disabled' && isOpen &&
            <MyAlert className={`${announcementType} backdrop`}>
                {announcementType === 'party'
                    ? <LuPartyPopper />
                    : announcementType === 'update'
                    ? <LuMegaphone />
                    : announcementType === 'info'
                    ? <LuInfo />
                    : announcementType === 'success'
                    ? <LuCircleCheck />
                    : announcementType === 'warning'
                    ? <LuCircleAlert />
                    : <LuTriangleAlert />
                }

                <div>
                    {parser.toReact(announcementMessage)}
                </div>


                {String(announcementCloseable) === 'true' &&
                <button className={'ml-auto p-2 hover:bg-white/20 duration-300 rounded'} onClick={handleClose}>
                    <LuCircleX />
                </button>}
            </MyAlert>
            }
        </div>
    );
};

export default Alert;
