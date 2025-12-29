<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import TicTacToeLogo from '@/components/TicTacToeLogo.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store as loginStore } from '@/routes/login';
import { request } from '@/routes/password';
import { store as registerStore } from '@/routes/register';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight, User } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Login or Register" />

    <div class="flex min-h-screen items-center justify-center bg-muted p-6">
        <div class="w-full max-w-4xl">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link href="/" class="flex items-center gap-2">
                    <TicTacToeLogo class="size-8 text-yellow-500" />
                    <span class="text-2xl font-medium">tictactoe</span>
                </Link>
            </div>

            <!-- Main Card Container -->
            <div class="rounded-xl bg-card shadow-lg">
                <div class="grid md:grid-cols-2">
                    <!-- Register Section (Left) -->
                    <div
                        class="border-b border-border p-8 md:border-r md:border-b-0"
                    >
                        <div class="mb-6 flex items-center gap-2">
                            <User class="size-4 text-muted-foreground" />
                            <h2
                                class="text-lg font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Register
                            </h2>
                        </div>

                        <Form
                            v-bind="registerStore.form()"
                            :reset-on-success="[
                                'password',
                                'password_confirmation',
                            ]"
                            v-slot="{ errors, processing }"
                            class="flex flex-col gap-6"
                        >
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="register-name" class="sr-only"
                                        >Username</Label
                                    >
                                    <Input
                                        id="register-name"
                                        type="text"
                                        name="name"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="name"
                                        placeholder="username"
                                        class="bg-muted"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="register-email" class="sr-only"
                                        >Email</Label
                                    >
                                    <Input
                                        id="register-email"
                                        type="email"
                                        name="email"
                                        required
                                        :tabindex="2"
                                        autocomplete="email"
                                        placeholder="email"
                                        class="bg-muted"
                                    />
                                    <InputError :message="errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        for="register-password"
                                        class="sr-only"
                                        >Password</Label
                                    >
                                    <Input
                                        id="register-password"
                                        type="password"
                                        name="password"
                                        required
                                        :tabindex="3"
                                        autocomplete="new-password"
                                        placeholder="password"
                                        class="bg-muted"
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        for="register-password-confirmation"
                                        class="sr-only"
                                        >Verify password</Label
                                    >
                                    <Input
                                        id="register-password-confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        :tabindex="4"
                                        autocomplete="new-password"
                                        placeholder="verify password"
                                        class="bg-muted"
                                    />
                                    <InputError
                                        :message="errors.password_confirmation"
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    variant="secondary"
                                    class="mt-2 w-full"
                                    :tabindex="5"
                                    :disabled="processing"
                                >
                                    <Spinner v-if="processing" />
                                    <User class="mr-2 size-4" />
                                    sign up
                                </Button>
                            </div>
                        </Form>
                    </div>

                    <!-- Login Section (Right) -->
                    <div class="p-8">
                        <div class="mb-6 flex items-center gap-2">
                            <ArrowRight class="size-4 text-muted-foreground" />
                            <h2
                                class="text-lg font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Login
                            </h2>
                        </div>

                        <div
                            v-if="status"
                            class="mb-4 text-center text-sm font-medium text-green-600"
                        >
                            {{ status }}
                        </div>

                        <Form
                            v-bind="loginStore.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="flex flex-col gap-6"
                        >
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="login-email" class="sr-only"
                                        >Email</Label
                                    >
                                    <Input
                                        id="login-email"
                                        type="email"
                                        name="email"
                                        required
                                        :tabindex="6"
                                        autocomplete="email"
                                        placeholder="email"
                                        class="bg-muted"
                                    />
                                    <InputError :message="errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Label
                                            for="login-password"
                                            class="sr-only"
                                            >Password</Label
                                        >
                                        <TextLink
                                            v-if="canResetPassword"
                                            :href="request()"
                                            class="text-xs text-muted-foreground"
                                            :tabindex="10"
                                        >
                                            forgot password?
                                        </TextLink>
                                    </div>
                                    <Input
                                        id="login-password"
                                        type="password"
                                        name="password"
                                        required
                                        :tabindex="7"
                                        autocomplete="current-password"
                                        placeholder="password"
                                        class="bg-muted"
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <div class="flex items-center gap-2">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        :tabindex="8"
                                    />
                                    <Label
                                        for="remember"
                                        class="cursor-pointer text-sm text-muted-foreground"
                                    >
                                        remember me
                                    </Label>
                                </div>

                                <Button
                                    type="submit"
                                    class="mt-2 w-full"
                                    :tabindex="9"
                                    :disabled="processing"
                                >
                                    <Spinner v-if="processing" />
                                    <ArrowRight class="mr-2 size-4" />
                                    sign in
                                </Button>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
