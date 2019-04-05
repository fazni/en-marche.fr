import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';
import { changeStateFieldVisibility } from '../services/form/changeStateFieldVisibility';

export default (countrySelector, stateSelector) => {
    const country = dom('#'+countrySelector);
    const state = dom('#'+stateSelector);

    (new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#app_procuration_proposal_address'),
            dom('#app_procuration_proposal_postalCode'),
            dom('#app_procuration_proposal_cityName'),
            null,
            dom('#app_procuration_proposal_country')
        )
    )).buildWidget();

    on(country, 'change', changeStateFieldVisibility(country, state));
};
