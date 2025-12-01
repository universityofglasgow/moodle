/**
 * Styling config for FormKit
 * See,  https://formkit.com/essentials/styling
 */

import { generateClasses } from '@formkit/themes'

const config = {
  config: {
    classes: generateClasses({
      global: { // classes
        outer: '$reset my-1',
        input: 'form-control',
        label: '$reset mb-0 mt-2 font-weight-bold',
        legend: '$reset mb-0 fs-1',
        help: 'form-text',
        messages: 'list-unstyled mt-1',
        message: 'text-danger',
      },
      form: {
        form: "mt-5 mx-auto p-5 border rounded"
      },
      range: {
        input: '$reset form-range',
      },
      submit: {
        outer: '$reset mt-3',
        input: '$reset btn btn-primary'
      },
      checkbox: {
        outer: '$reset form-check',
        input: '$reset form-check-input',
      },
      radio: {
        legend: '$reset mb-0 font-weight-bold',
        label: '$reset ml-1',
        outer: '$reset form-check form-check-inline',
        input: '$reset form-check-input',
        options: '$reset list-unstyled mb-0',
        option: '$reset pr-3'
      },
    })
  } 
}

export default config