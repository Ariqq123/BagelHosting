import {
    Chart as ChartJS,
    ChartData,
    ChartDataset,
    ChartOptions,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { DeepPartial } from 'ts-essentials';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import { useEffect, useRef, useState } from 'react';
import { deepmerge, deepmergeCustom } from 'deepmerge-ts';
import { theme } from 'twin.macro';
import { hexToRgba } from '@/lib/helpers';

ChartJS.register(LineElement, PointElement, Filler, LinearScale, Tooltip);

const options: ChartOptions<'line'> = {
    responsive: true,
    animation: false,
    plugins: {
        tooltip: {
            enabled: true,
            intersect: false,   
            position: 'nearest',
            displayColors: false,
            bodyColor: '#B2B2C1',
            xAlign: 'center',
            yAlign: 'bottom',
            cornerRadius: 5,
            padding: 10,
            backgroundColor: '#42425B',
            bodyFont: {
                weight: '600',
            },
            callbacks: {
                title: function () {
                    return '';
                },
            },
        },
        legend: { display: false },
        title: { display: false },
    },
    layout: {
        padding: 0,
    },
    scales: {
        x: {
            min: 0,
            max: 19,
            type: 'linear',
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                display: false,
            },
        },
        y: {
            min: 0,
            type: 'linear',
            grid: {
                display: false,
                color: theme('colors.gray.700'),
                drawBorder: false,
            },
            ticks: {
                display: false,
                count: 3,
                color: theme('colors.gray.200'),
                font: {
                    family: theme('fontFamily.sans'),
                    size: 11,
                    weight: '400',
                },
            },
        },
    },
    elements: {
        point: {
            radius: 0,
        },
        line: {
            tension: 0.35,
        },
    },
};

function getOptions(opts?: DeepPartial<ChartOptions<'line'>> | undefined): ChartOptions<'line'> {
    return deepmerge(options, opts || {});
}

type ChartDatasetCallback = (value: ChartDataset<'line'>, index: number) => ChartDataset<'line'>;

function getEmptyData(label: string, sets = 1, callback?: ChartDatasetCallback | undefined): ChartData<'line'> {
    const next = callback || ((value) => value);
    const primary = useStoreState((state: ApplicationStore) => state.settings.data!.arix.primary);

    return {
        labels: Array(20)
            .fill(0)
            .map((_, index) => index),
        datasets: Array(sets)
            .fill(0)
            .map((_, index) =>
                next(
                    {
                        fill: true,
                        label: label,
                        data: Array(20).fill(-5),
                        borderColor: primary,
                        backgroundColor: hexToRgba(primary, 0.5),
                    },
                    index
                )
            ),
    };
}

const merge = deepmergeCustom({ mergeArrays: false });

interface UseChartOptions {
    sets: number;
    options?: DeepPartial<ChartOptions<'line'>> | number | undefined;
    callback?: ChartDatasetCallback | undefined;
}

function useChart(label: string, opts?: UseChartOptions) {
    const options = getOptions(
        typeof opts?.options === 'number' ? { scales: { y: { min: 0, suggestedMax: opts.options } } } : opts?.options
    );
    const [data, setData] = useState(getEmptyData(label, opts?.sets || 1, opts?.callback));
    const animationFrame = useRef<number | null>(null);

    const cancelAnimation = () => {
        if (animationFrame.current !== null) {
            cancelAnimationFrame(animationFrame.current);
            animationFrame.current = null;
        }
    };

    useEffect(() => cancelAnimation, []);

    const push = (items: number | null | (number | null)[]) => {
        cancelAnimation();

        setData((state) =>
            merge(state, {
                datasets: (Array.isArray(items) ? items : [items]).map((item, index) => ({
                    ...state.datasets[index],
                    data: state.datasets[index].data
                        .slice(1)
                        .concat(typeof item === 'number' ? Number(item.toFixed(2)) : item),
                })),
            })
        );
    };

    const pushSmooth = (items: number | null | (number | null)[], duration = 900) => {
        cancelAnimation();

        const targets = Array.isArray(items) ? items : [items];
        let starts: (number | null)[] = [];
        const startedAt = performance.now();

        setData((state) =>
            merge(state, {
                datasets: targets.map((item, index) => {
                    const previous = state.datasets[index].data[state.datasets[index].data.length - 1];
                    const start = typeof previous === 'number' && previous >= 0 ? previous : item;
                    starts[index] = typeof start === 'number' ? start : item;

                    return {
                        ...state.datasets[index],
                        data: state.datasets[index].data.slice(1).concat(starts[index]),
                    };
                }),
            })
        );

        const tick = (now: number) => {
            const progress = Math.min(1, (now - startedAt) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);

            setData((state) =>
                merge(state, {
                    datasets: targets.map((target, index) => {
                        const data = [...state.datasets[index].data];
                        const start = starts[index];
                        const next =
                            typeof target === 'number' && typeof start === 'number'
                                ? Number((start + (target - start) * eased).toFixed(2))
                                : target;

                        data[data.length - 1] = next;

                        return {
                            ...state.datasets[index],
                            data,
                        };
                    }),
                })
            );

            if (progress < 1) {
                animationFrame.current = requestAnimationFrame(tick);
            } else {
                animationFrame.current = null;
            }
        };

        animationFrame.current = requestAnimationFrame(tick);
    };

    const clear = () => {
        cancelAnimation();

        setData((state) =>
            merge(state, {
                datasets: state.datasets.map((value) => ({
                    ...value,
                    data: Array(20).fill(-5),
                })),
            })
        );
    };

    return { props: { data, options }, push, pushSmooth, clear };
}

function useChartTickLabel(label: string, max: number, tickLabel: string, roundTo?: number) {
    return useChart(label, {
        sets: 1,
        options: {
            scales: {
                y: {
                    suggestedMax: max,
                    ticks: {
                        callback(value) {
                            return `${roundTo ? Number(value).toFixed(roundTo) : value}${tickLabel}`;
                        },
                    },
                },
            },
        },
    });
}

export { useChart, useChartTickLabel, getOptions, getEmptyData };
