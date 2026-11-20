<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    name: string;
    email: string;
    acceptUrl: string;
    copy: {
        title: string;
        intro: string;
        email: string;
        password: string;
        password_confirmation: string;
        submit: string;
    };
}>();
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-stone-50 p-4 text-stone-900 dark:bg-stone-950 dark:text-stone-100"
    >
        <Head :title="copy.title" />
        <main
            class="w-full max-w-sm rounded-xl bg-white p-6 shadow-sm ring-1 ring-stone-200 dark:bg-stone-900 dark:ring-stone-800"
        >
            <h1 class="text-xl font-semibold">{{ copy.title }}</h1>
            <p class="mt-1 text-sm text-stone-600 dark:text-stone-400">
                {{ name }} · {{ copy.intro }}
            </p>

            <Form
                :action="acceptUrl"
                method="post"
                class="mt-6 flex flex-col gap-4"
                #default="{ errors, processing }"
            >
                <label class="flex flex-col gap-1 text-sm">
                    <span class="font-medium">{{ copy.email }}</span>
                    <input
                        type="email"
                        :value="email"
                        readonly
                        autocomplete="username"
                        class="rounded-lg border border-stone-300 bg-stone-100 px-3 py-2 dark:border-stone-700 dark:bg-stone-800"
                    />
                </label>

                <label class="flex flex-col gap-1 text-sm">
                    <span class="font-medium">{{ copy.password }}</span>
                    <input
                        v-focus
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950"
                    />
                    <span
                        v-if="errors.password"
                        class="text-red-600 dark:text-red-400"
                        >{{ errors.password }}</span
                    >
                </label>

                <label class="flex flex-col gap-1 text-sm">
                    <span class="font-medium">{{
                        copy.password_confirmation
                    }}</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950"
                    />
                </label>

                <button
                    type="submit"
                    :disabled="processing"
                    class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white disabled:opacity-50 dark:bg-white dark:text-stone-900"
                >
                    {{ copy.submit }}
                </button>
            </Form>
        </main>
    </div>
</template>
