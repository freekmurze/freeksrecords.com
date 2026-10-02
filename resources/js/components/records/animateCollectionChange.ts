import { prefersReducedMotion } from './recordMotion';

function visibleSleeves(container: HTMLElement): HTMLElement[] {
    return Array.from(
        container.querySelectorAll<HTMLElement>('.catalog-record-content'),
    ).filter((element) => {
        const bounds = element.getBoundingClientRect();
        return bounds.bottom > 0 && bounds.top < window.innerHeight;
    });
}

export function animateCollectionChange(
    container: HTMLElement,
    update: () => void,
): () => void {
    if (prefersReducedMotion()) {
        update();
        return () => {};
    }

    let cancelled = false;
    const animations: Animation[] = [];
    const outgoing = visibleSleeves(container);
    const previousBounds = container
        .querySelector('.shelf-results')
        ?.getBoundingClientRect();
    const hadIndex = Boolean(container.querySelector('.collection-index'));
    container.dataset.shelfChanging = 'true';

    for (const content of outgoing) {
        animations.push(
            content.animate(
                [
                    { opacity: 1, transform: 'translateY(0)' },
                    { opacity: 0, transform: 'translateY(7px)' },
                ],
                { duration: 100, easing: 'ease-in', fill: 'forwards' },
            ),
        );
    }

    void Promise.allSettled(animations.map((animation) => animation.finished))
        .then(() => {
            if (cancelled) return;

            update();
            animations.forEach((animation) => animation.cancel());
            animations.length = 0;

            const cupboard =
                container.querySelector<HTMLElement>('.shelf-results');
            const bounds = cupboard?.getBoundingClientRect();
            const resizing =
                previousBounds &&
                bounds &&
                Math.abs(previousBounds.width - bounds.width) > 1;
            const main =
                container.querySelector<HTMLElement>('.collection-main');
            if (main && previousBounds && bounds && resizing) {
                animations.push(
                    main.animate(
                        [
                            {
                                width: `${previousBounds.width}px`,
                                transform: `translate(${previousBounds.left - bounds.left}px, ${previousBounds.top - bounds.top}px)`,
                            },
                            {
                                width: `${main.getBoundingClientRect().width}px`,
                                transform: 'none',
                            },
                        ],
                        { duration: 360, easing: 'cubic-bezier(.4, 0, .2, 1)' },
                    ),
                );
            }
            const indexPanel =
                container.querySelector<HTMLElement>('.collection-index');
            if (indexPanel && !hadIndex) {
                animations.push(
                    indexPanel.animate(
                        [
                            { opacity: 0, transform: 'translateX(-12px)' },
                            { opacity: 1, transform: 'none' },
                        ],
                        {
                            duration: 220,
                            delay: 100,
                            fill: 'backwards',
                            easing: 'ease-out',
                        },
                    ),
                );
            }
            const heading = container.querySelector<HTMLElement>(
                '.collection-heading',
            );
            if (heading) {
                animations.push(
                    heading.animate([{ opacity: 0 }, { opacity: 1 }], {
                        duration: 220,
                        delay: resizing ? 100 : 0,
                        fill: 'backwards',
                    }),
                );
            }

            for (const [index, content] of visibleSleeves(
                container,
            ).entries()) {
                animations.push(
                    content.animate(
                        [
                            { opacity: 0, transform: 'translateY(9px)' },
                            { opacity: 1, transform: 'translateY(0)' },
                        ],
                        {
                            duration: 190,
                            delay:
                                (resizing ? 100 : 0) + Math.min(index * 12, 48),
                            easing: 'cubic-bezier(.2, .7, .25, 1)',
                            fill: 'backwards',
                        },
                    ),
                );
            }

            return Promise.allSettled(
                animations.map((animation) => animation.finished),
            );
        })
        .then(() => {
            if (!cancelled) delete container.dataset.shelfChanging;
        });

    return () => {
        cancelled = true;
        animations.forEach((animation) => animation.cancel());
        delete container.dataset.shelfChanging;
    };
}
