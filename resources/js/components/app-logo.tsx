import { cn } from '@/lib/utils';

type AppLogoProps = {
    className?: string;
    appearance?: 'light' | 'dark' | 'auto';
};

const brandNavy = '#0a2446';
const brandOrange = '#fd9d00';
const emblemPath = '/images/brand/dap/emblem-requisitions.png';

function DapLettering({ color }: { color: string }) {
    return (
        <g
            fill={color}
            fillRule="evenodd"
            stroke={color}
            strokeWidth={4}
            strokeLinejoin="round"
        >
            <path d="M697 149H831A97 97 0 0 1 831 343H697Z M756 201H825A45 45 0 0 1 825 291H756Z" />
            <path d="M918 343L1024.6 170Q1054 122.6 1083.4 170L1190 343H1133L1054 222L975 343Z" />
            <path d="M1210 149H1366A70 70 0 0 1 1366 289H1267V343H1210Z M1267 201H1358A21 21 0 0 1 1358 243H1267Z" />
        </g>
    );
}

export function SidebarLogo() {
    return (
        <span aria-hidden="true" className="flex min-w-0 flex-col gap-1">
            <span className="flex items-center gap-2">
                <img
                    src={emblemPath}
                    alt=""
                    width={666}
                    height={531}
                    className="h-auto w-10 shrink-0 group-data-[collapsible=icon]:w-8"
                />
                <svg
                    viewBox="695 145 745 202"
                    className="h-auto w-16 text-brand-navy group-data-[collapsible=icon]:hidden dark:text-sidebar-foreground"
                >
                    <DapLettering color="currentColor" />
                </svg>
            </span>
            <span className="text-[11px] leading-tight whitespace-nowrap text-sidebar-foreground group-data-[collapsible=icon]:hidden">
                Equipment Requisitions Administration
            </span>
        </span>
    );
}

/**
 * Shared emblem and vector lettering for light and dark backgrounds.
 */
function LogoArtwork({
    className,
    appearance,
}: {
    className?: string;
    appearance: 'light' | 'dark';
}) {
    const letteringColor = appearance === 'dark' ? '#ffffff' : brandNavy;

    return (
        <svg
            viewBox="0 0 1575 563"
            role="img"
            aria-label="DAP — Equipment Requisitions Administration"
            className={className}
        >
            <image href={emblemPath} x={18} y={16} width={666} height={531} />
            <DapLettering color={letteringColor} />
            <rect
                x={618}
                y={393}
                width={62}
                height={11}
                rx={5.5}
                fill={brandOrange}
            />
            <rect
                x={1490}
                y={393}
                width={63}
                height={11}
                rx={5.5}
                fill={brandOrange}
            />
            <text
                x={700}
                y={410}
                textLength={770}
                lengthAdjust="spacingAndGlyphs"
                fill={letteringColor}
                fontSize={44}
                fontWeight={600}
                className="font-sans"
            >
                Equipment Requisitions Administration
            </text>
        </svg>
    );
}

export default function AppLogo({
    className,
    appearance = 'auto',
}: AppLogoProps) {
    return (
        <span
            className={cn('inline-block w-40 max-w-full shrink-0', className)}
        >
            {appearance !== 'dark' && (
                <LogoArtwork
                    appearance="light"
                    className={cn(
                        'h-auto w-full',
                        appearance === 'auto' && 'dark:hidden',
                    )}
                />
            )}
            {appearance !== 'light' && (
                <LogoArtwork
                    appearance="dark"
                    className={cn(
                        'h-auto w-full',
                        appearance === 'auto' && 'hidden dark:block',
                    )}
                />
            )}
        </span>
    );
}
