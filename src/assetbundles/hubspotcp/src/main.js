import {createApp, ref} from 'vue'
import StoreSelector from './components/StoreSelector.vue'
import FieldMapper from './components/FieldMapper/FieldMapper.vue'

Craft.HubSpot = {}

Craft.HubSpot.StoreSelector = Garnish.Base.extend({
        init: function (settings) {
            this.setSettings(settings, Craft.HubSpot.StoreSelector.defaults);

            const props = this.settings;
            const stores = ref(props.stores)
            const app = createApp(StoreSelector, {
                stores: stores,
                value: props.value,
                name: props.name
            })
            const vm = app.mount(this.settings.container)
        },
    },
    {
        defaults: {
            container: null,
            stores: [],
            value: null,
            name: null
        }
    });


Craft.HubSpot.FieldMapper = Garnish.Base.extend({
        init: function (settings) {
            this.setSettings(settings, Craft.HubSpot.FieldMapper.defaults);

            const props = this.settings.props;
            const app = createApp(FieldMapper, props)
            const vm = app.mount(this.settings.container)
        },
    },
    {
        defaults: {
            container: null,
            props: {
                mapper: null
            }
        }
    });