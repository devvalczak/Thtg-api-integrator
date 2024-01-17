## THTG API integrator for WordPress

### Installation

Download the latest version package (*.zip) and simply install it in WordPress.

### Usage

1. Install package
2. Activate `Thtg API form integrator` plugin in plugins menu
3. Go to `settings` > `Thtg API integrator`
4. Input endpoint for your public API
5. Click `add new integration`
6. Fill `form ID` - This is your form `id` HTML attribute on your WordPress website
7. Fill `branch` text field
8. Choose `integration`
9. Fill `callback` text field - this is the callback that will be called when the request has been completed
10. (Optionally) Fill `JS` text field - in this text field, you can write your own script to be injected into the form page. Here you should add your callback handler, e.g:

```
function callback(response) {
   console.log(response);
   // handle your response
}
```

10. You are ready to roll. Enjoy!
