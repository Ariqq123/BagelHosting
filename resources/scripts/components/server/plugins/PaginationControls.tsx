import React from 'react';
import { Button } from '@/components/elements/button/index';

interface Props {
    page: number;
    searching: boolean;
    hasNext: boolean;
    onPrevious: () => void;
    onNext: () => void;
}

export default ({ page, searching, hasNext, onPrevious, onNext }: Props) => (
    <div className={'flex justify-between'}>
        <Button.Text disabled={page <= 1 || searching} onClick={onPrevious}>
            Previous
        </Button.Text>
        <span className={'text-sm text-gray-300 py-2'}>Page {page}</span>
        <Button.Text disabled={searching || !hasNext} onClick={onNext}>
            Next
        </Button.Text>
    </div>
);
