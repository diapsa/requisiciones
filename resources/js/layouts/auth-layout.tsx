import { Link } from '@inertiajs/react';
import { Activity, ClipboardCheck, FileUp, Thermometer } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

/**
 * Floating tiles around the logo, echoing the icons of the DAP brand.
 */
const floatingIcons = [
    {
        icon: ClipboardCheck,
        position: '-top-8 left-4',
        tone: 'navy',
        delay: '0s',
    },
    {
        icon: Thermometer,
        position: '-top-4 -right-6',
        tone: 'orange',
        delay: '1.6s',
    },
    {
        icon: Activity,
        position: '-bottom-10 left-1/4',
        tone: 'orange',
        delay: '3.2s',
    },
    {
        icon: FileUp,
        position: '-right-2 -bottom-6',
        tone: 'navy',
        delay: '0.8s',
    },
] as const;

export default function AuthLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative grid min-h-svh lg:grid-cols-[1.15fr_1fr]">
            {/* Franja de marca superior */}
            <div
                aria-hidden
                className="absolute inset-x-0 top-0 z-20 h-1 bg-linear-to-r from-brand-orange via-brand-orange to-brand-navy"
            />

            {/* Panel de marca (solo escritorio) */}
            <aside className="relative hidden overflow-hidden bg-white p-12 lg:flex lg:flex-col dark:bg-background">
                {/* Halos de color */}
                <div
                    aria-hidden
                    className="pointer-events-none absolute -top-40 -right-40 size-[34rem] rounded-full bg-brand-orange/25 blur-3xl"
                />
                <div
                    aria-hidden
                    className="pointer-events-none absolute -bottom-48 -left-40 size-[32rem] rounded-full bg-brand-navy/15 blur-3xl"
                />

                <div className="relative z-10 flex flex-1 items-center justify-center">
                    <div className="relative w-full max-w-xl animate-in duration-700 zoom-in-95 fade-in motion-reduce:animate-none">
                        {/* Órbitas, como el trazo naranja del logo */}
                        <svg
                            aria-hidden
                            viewBox="0 0 600 400"
                            className="pointer-events-none absolute inset-0 size-full scale-[1.35] overflow-visible"
                        >
                            <ellipse
                                cx="300"
                                cy="200"
                                rx="290"
                                ry="150"
                                fill="none"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeDasharray="2 12"
                                transform="rotate(-10 300 200)"
                                className="stroke-brand-orange/70"
                            />
                            <ellipse
                                cx="300"
                                cy="200"
                                rx="260"
                                ry="190"
                                fill="none"
                                strokeWidth="1.5"
                                transform="rotate(16 300 200)"
                                className="stroke-brand-navy/20"
                            />
                        </svg>

                        <Link href={home()} className="relative block p-10">
                            <AppLogo className="w-full" />
                        </Link>

                        {floatingIcons.map(
                            ({ icon: Icon, position, tone, delay }) => (
                                <span
                                    key={position}
                                    aria-hidden
                                    style={{ animationDelay: delay }}
                                    className={cn(
                                        'absolute grid size-14 animate-float place-items-center rounded-2xl shadow-lg ring-1 motion-reduce:animate-none',
                                        tone === 'navy'
                                            ? 'bg-brand-navy text-brand-orange shadow-brand-navy/25 ring-white/10'
                                            : 'bg-white text-brand-orange shadow-brand-orange/25 ring-brand-orange/20 dark:bg-card',
                                        position,
                                    )}
                                >
                                    <Icon className="size-6" />
                                </span>
                            ),
                        )}
                    </div>
                </div>

                <p className="relative z-10 text-xs text-muted-foreground">
                    © {new Date().getFullYear()} DIAPSA
                </p>
            </aside>

            {/* Formulario */}
            <main className="relative flex items-center justify-center overflow-hidden bg-slate-50 p-6 md:p-10 lg:border-l dark:bg-muted/20">
                <div
                    aria-hidden
                    className="pointer-events-none absolute -right-32 -bottom-32 size-96 rounded-full bg-brand-orange/10 blur-3xl"
                />

                <div className="relative w-full max-w-md animate-in duration-500 fade-in slide-in-from-bottom-4 motion-reduce:animate-none">
                    <Link
                        href={home()}
                        className="mb-8 flex justify-center lg:hidden"
                    >
                        <AppLogo className="w-60" />
                    </Link>

                    <div className="overflow-hidden rounded-2xl border bg-card shadow-xl shadow-brand-navy/10">
                        <div
                            aria-hidden
                            className="h-1.5 bg-linear-to-r from-brand-orange to-brand-navy"
                        />
                        <div className="p-8 sm:p-10">
                            <div className="mb-8 space-y-2">
                                <h1 className="text-2xl font-semibold tracking-tight text-brand-navy dark:text-foreground">
                                    {title}
                                </h1>
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                            {children}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}
