<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth'
import { usePage, router } from '@inertiajs/vue3'

const { userId } = useAuth()

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    uname: '',
    password: '',
    remember: false,
});


const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password')
    });
};

</script>

<template>
    <AuthBase title="Welcome to OPAIS" description="Enter your uname and password below to log in">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>


        <form @submit.prevent="submit" class="space-y-5 text-white">
            <div>
                <Label class="text-green-100 text-xs tracking-widest uppercase">
                    Username
                </Label> <Input v-model="form.uname"
                    class="bg-white/10 border-white/20 text-white placeholder:text-green-200"
                    placeholder="Enter your username" />
            </div>

            <div>
                <Label class="text-green-100 text-xs tracking-widest uppercase">
                    Password
                </Label> <Input type="password" v-model="form.password"
                    class="bg-white/10 border-white/20 text-white placeholder:text-green-200"
                    placeholder="Enter your password" />
            </div>

            <Button class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold">
                SIGN IN
            </Button>

        </form>
    </AuthBase>
</template>
