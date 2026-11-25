import type { ComponentProps } from 'react';

type AppLogoIconProps = Omit<ComponentProps<'img'>, 'src' | 'width' | 'height'>;

export default function AppLogoIcon({
    alt = 'DAP',
    ...props
}: AppLogoIconProps) {
    return (
        <img
            {...props}
            src="/images/brand/dap/app-icon-requisitions.png"
            alt={alt}
            width={128}
            height={128}
        />
    );
}
