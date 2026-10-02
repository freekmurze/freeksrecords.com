declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            [key: string]: unknown;
        };
    }
}

export {};
