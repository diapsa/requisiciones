import { Form, Head, Link } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/InvitationController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';

type Props = {
    token: string;
    email: string;
    name: string | null;
    isValid: boolean;
    passwordRules: string;
};

/**
 * Where an invited user chooses their password. The link came by email, so choosing it also activates the account.
 */
export default function AcceptInvitation({
    token,
    email,
    name,
    isValid,
    passwordRules,
}: Props) {
    if (!isValid) {
        return (
            <>
                <Head title="Invitación no válida" />

                <div className="grid gap-6 text-center">
                    <div className="grid gap-2 text-sm text-muted-foreground">
                        <p className="font-medium text-foreground">
                            Este enlace venció o ya se usó.
                        </p>
                        <p>
                            Si ya configuraste tu contraseña, inicia sesión. Si
                            no, pide al administrador que te reenvíe la
                            invitación.
                        </p>
                    </div>
                    <Button asChild className="w-full">
                        <Link href={login()}>Ir a iniciar sesión</Link>
                    </Button>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Activa tu cuenta" />

            {name && (
                <p className="mb-6 text-center text-sm">
                    Hola, <span className="font-medium">{name}</span>. Elige la
                    contraseña con la que vas a entrar.
                </p>
            )}

            <Form
                {...store.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnError={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="email">Correo</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">Contraseña</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                required
                                autoFocus
                                passwordrules={passwordRules}
                                aria-invalid={Boolean(errors.password)}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                Confirma la contraseña
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                autoComplete="new-password"
                                required
                                passwordrules={passwordRules}
                            />
                        </div>

                        <Button
                            type="submit"
                            className="mt-2 w-full"
                            disabled={processing}
                        >
                            {processing && <Spinner />}
                            Activar mi cuenta
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

AcceptInvitation.layout = {
    title: 'Activa tu cuenta',
    description: 'Configura la contraseña con la que vas a entrar al sistema.',
};
