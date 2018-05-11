title: Syntax Reference
description: A tiny PHP templating engine.

<h1 id="syntax">Syntax</h1>

## Variables
```
{{ variableName }}
```
Will be replaced with either the value of that variable taken from the user context or
from the system context. If no value could be found the expression is taken out of the template.

```
{{ variableName.key.foo.bar }}
```
You can use dot-notation for variable names. This assumes every key is an array until the last,
which is used for replacement. If any error occurs, the expression is also taken out of the template.

## Callables
If a variable can be identified as a callable interface, that function will be invoked
and it's return value is used for replacement.

## Conditions
```
{{if variableName}}
  ...
{{/if}}
```
Will only render it's contents if the corresponding variable evaluates to true.

```
{{if variableName}}
  ...
{{else}}
  ...
{{/if}}
```
If defined and the condition doesn't met, the else block will be rendered.

```
{{variableName ? alternate1 : alternate2}}
```
You may use ternary operators. This checks the first given variable. If it resolves
to `true`, `alternate1` is used for replacement, otherwise `alternate2`.

## Comments
```
{* This is an awesome comment and doesn't appear in the rendered template *}
```
Like this you can write comments within the code. These will be totally removed, regardingless of contents.
