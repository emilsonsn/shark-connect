<script setup>
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import StandardPageLayout from '../../Layouts/StandardPageLayout.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

</script>

<template>
    <StandardPageLayout title="Profile">

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />

                    <SectionBorder />
                </div>

            </div>
        </div>
    </StandardPageLayout>
</template>
