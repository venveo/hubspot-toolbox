/* global Craft */

import axios from 'axios'

export default {
    saveStore(id, label, adminUri) {
        return axios.post(Craft.getActionUrl('hubspot-toolbox/ecommerce/save-store'),
            {
                id,
                label,
                adminUri
            },
            {
                headers: {
                    'X-CSRF-Token': Craft.csrfTokenValue,
                }
            });
    },

    getMappings(mapper, sourceTypeId, previewObjectId) {
        return axios.get(Craft.getActionUrl('hubspot-toolbox/property-mappers/get-mappings', {mapper, sourceTypeId, previewObjectId}))
    },

    saveMapping(mapping, mapper, sourceTypeId, property, previewObjectId = null) {
        return axios.post(Craft.getActionUrl('hubspot-toolbox/property-mappers/save-mapping'), {
            mapping,
            mapper,
            property,
            sourceTypeId,
            previewObjectId
        }, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    publishObjectMappings(mapper, sourceTypeId) {
        return axios.post(Craft.getActionUrl('hubspot-toolbox/property-mappers/publish'), {
            mapper,
            sourceTypeId
        }, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    },

    deleteMapping(mappingId) {
        return axios.post(Craft.getActionUrl('hubspot-toolbox/property-mappers/delete-mapping'), {
            id: mappingId,
        }, {
            headers: {
                'X-CSRF-Token': Craft.csrfTokenValue,
            }
        })
    }
}