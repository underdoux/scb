import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import NavLink from '@/Components/NavLink.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Toast from '@/Components/Toast.vue';

export default {
    install(app) {
        // Register components globally
        app.component('ApplicationLogo', ApplicationLogo);
        app.component('Checkbox', Checkbox);
        app.component('DangerButton', DangerButton);
        app.component('Dropdown', Dropdown);
        app.component('DropdownLink', DropdownLink);
        app.component('InputError', InputError);
        app.component('InputLabel', InputLabel);
        app.component('Modal', Modal);
        app.component('NavLink', NavLink);
        app.component('Pagination', Pagination);
        app.component('PrimaryButton', PrimaryButton);
        app.component('ResponsiveNavLink', ResponsiveNavLink);
        app.component('SecondaryButton', SecondaryButton);
        app.component('TextInput', TextInput);
        app.component('Toast', Toast);
    },
};
