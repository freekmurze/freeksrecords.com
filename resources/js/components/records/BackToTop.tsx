import { ArrowUp } from 'lucide-react';
import { useEffect, useState } from 'react';

import { motionScrollBehavior } from '@/components/records/recordMotion';

export function BackToTop() {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const update = () => setVisible(window.scrollY > 400);
        update();
        window.addEventListener('scroll', update, { passive: true });
        return () => window.removeEventListener('scroll', update);
    }, []);

    return (
        <button
            className={`back-to-top ${visible ? 'is-visible' : ''}`}
            type="button"
            aria-label="Back to top"
            tabIndex={visible ? 0 : -1}
            aria-hidden={!visible}
            onClick={() => {
                window.scrollTo({
                    top: 0,
                    behavior: motionScrollBehavior(),
                });
                document
                    .querySelector<HTMLAnchorElement>('.collection-brand')
                    ?.focus({ preventScroll: true });
            }}
        >
            <ArrowUp aria-hidden="true" size={18} /> Top
        </button>
    );
}
