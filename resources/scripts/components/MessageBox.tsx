import * as React from 'react';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import { CheckCircleIcon, InformationCircleIcon, ExclamationCircleIcon, ExclamationIcon } from '@heroicons/react/outline'

export type FlashMessageType = 'success' | 'info' | 'warning' | 'error';

const TopAlert = styled.div`
    ${tw`fixed w-full px-4 py-3 rounded-component z-50 flex gap-x-3 items-center mx-2 opacity-0`};
    animation: fadeIn 6s;
    backdrop-filter:blur(8px);
    background-color:color-mix(in srgb, var(--gray600) 70%, transparent);

    & > .icon{
        ${tw`w-8 h-8 rounded flex items-center justify-center flex-shrink-0`}
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        10%{
            opacity: 1;
        }
      
        90% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
      }      
`;

interface Props {
    title?: string;
    children: string;
    type?: FlashMessageType;
}
const MessageBox = ({ title, children, type }: Props) => {
    const flashMessage = useStoreState((state: ApplicationStore) => state.settings.data!.arix.flashMessage);

    return (
        <TopAlert className={
            flashMessage == 1 
            ? 'max-w-[320px] top-4 right-4 text-gray-100'
            : `max-w-[400px] bottom-4 left-1/2 transform -translate-x-1/2 text-gray-50
                ${type == 'success' 
                ? '!bg-green-600/30' 
                : type == 'info' 
                ? '!bg-blue-600/30' 
                : type == 'error' 
                ? '!bg-red-600/30' 
                : type == 'warning' 
                ? '!bg-yellow-600/30'
                : ''}`
        }>
            {type == 'success'
            ? <div className={'icon bg-green-600'}>
                <CheckCircleIcon className={'w-5 text-green-300'}/>
            </div>
            : type == 'info'
            ?<div className={'icon bg-blue-600'}>
                <InformationCircleIcon className={'w-5 text-blue-300'}/>
            </div>
            : type == 'error'
            ? <div className={'icon bg-red-600'}>
                <ExclamationCircleIcon className={'w-5 text-red-300'}/>
            </div>
            : type == 'warning'
            ?<div className={'icon bg-yellow-600'}>
                <ExclamationIcon className={'w-5 text-yellow-300'}/>
            </div>
            : ''}
            <div>
                <p className={`font-semibold text-sm ${flashMessage == 2 ? 'hidden' : ''}`}>{title}</p>
                <p className={flashMessage == 2 ? 'text-gray-50' : ''}>{children}</p>
            </div>
        </TopAlert>
    )
};

MessageBox.displayName = 'MessageBox';

export default MessageBox;
