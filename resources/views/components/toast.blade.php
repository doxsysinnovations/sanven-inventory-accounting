<div x-data="toast()" x-show="visible" x-transition class="fixed bottom-4 right-4 z-50">
    <div :class="{
        'bg-green-500': type === 'success',
        'bg-red-500': type === 'error',
        'bg-blue-500': type === 'info'
    }" class="text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
        <span x-text="message" class="mr-4"></span>
        <button @click="hide()" class="text-white hover:text-gray-200">×</button>
    </div>
</div>

<script>
    function toast() {
        return {
            visible: false,
            type: '',
            message: '',
            show(type, message) {
                this.type = type;
                this.message = message;
                this.visible = true;
                setTimeout(() => this.hide(), 3000);
            },
            hide() {
                this.visible = false;
            }
        }
    }
</script>