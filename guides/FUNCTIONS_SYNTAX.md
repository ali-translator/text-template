### Functions Syntax

In our system, Functions provide a dynamic way to manipulate and format text. They utilize a syntax that closely resembles the pipe functionality in Unix-based systems, allowing for a chained or sequential application of multiple functions.

#### Basic Syntax:

```{functionName(some_variable_name, 'some static text')|anotherFunctionWithoutArguments()}```

#### Breaking it down:

1. All "Functions" have "(...arguments...)" at the end, or at least "()" without arguments. Example: ```{print('Hello world!')}```
2. **Pipe Operator "|"**: This serves as the "pipe" operator, similar to bash, which takes the output of one function and provides it as input to the next. You can chain as many functions as you need, in the order they should be executed.
3. **Function Names**: After the pipe | comes the function name. This is immediately followed by its parameters enclosed in parentheses ( ).
   - **Parameters**: Parameters can be either variables or strings.
   - **Strings**: Can be enclosed in single (') or double (") quotes: ```{print('hello')}```
   - **Variables**: Are not enclosed in quotes: ```{print(city_name)}```. Here, city_name is treated as a variable.
     Dot-path variables are also supported: ```{print(user.profile.city)}```.

#### Example:

```
{print('hello')|makeFirstCharacterInUppercase()}
```

The system will first execute the print function, which outputs the string "hello". 
This output is then piped into the makeFirstCharacterInUppercase function, resulting in the final output:

```
Hello
```

In another example, with a variable as an input:

```
{print(city_name)}
```

This will return the value of the city_name variable. (Of course, you don't need a "function" for normal variable output, you can just use {city_name}).


#### Chaining Multiple Functions:

Functions can be chained together.
The result of each function is passed on to the next function in the chain:

```
{function1()|function2()|function3()|...|functionN()}
```

With each pipe, the output is transformed step by step until the final result is achieved.

#### Functions inside nodes

Function syntax works the same way inside conditional nodes:

```
{% if is_daytime %}
  {print(user_name)|makeFirstCharacterInUppercase()}
{% endif %}
```
