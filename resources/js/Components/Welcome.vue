<script setup>

</script>

<template>
    <div>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                Bem vindo, {{ $page.props.auth.user.name }}!
            </h1>

        </div>

    </div>
</template>
