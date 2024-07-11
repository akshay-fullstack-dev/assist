<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'O :attribute deve ser aceito.',
    'active_url'           => 'O :attribute não é um URL válido.',
    'after'                => 'O :attribute deve ser uma data após :date.',
    'after_or_equal'       => 'O :attribute deve ser uma data posterior ou igual a :date.',
    'alpha'                => 'O :attribute pode conter apenas letras.',
    'alpha_dash'           => 'O :attribute pode conter apenas letras, números e traços.',
    'alpha_num'            => 'O :attribute pode conter apenas letras e números.',
    'array'                => 'O :attribute deve ser uma matriz.',
    'before'               => 'O :attribute deve ser uma data antes de :date.',
    'before_or_equal'      => 'O :attribute deve ser uma data anterior ou igual a :date.',
    'between'              => [
        'numeric' => 'O :attribute deve estar entre  :min e :max.',
        'file'    => 'O :attribute deve estar entre :min e :max kilobytes.',
        'string'  => 'O :attribute deve estar entre :min e :max caracteres.',
        'array'   => 'O :attribute deve ter entre :min e :max itens.',
    ],
    'boolean'              => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'A confirmação do :attribute não corresponde.',
    'date'                 => 'O :attribute não é uma data válida.',
    'date_format'          => 'O :attribute não corresponde ao formato: formato.',
    'different'            => 'O :attribute e: outro devem ser diferentes.',
    'digits'               => 'O :attribute deve ser: dígitos dígitos.',
    'digits_between'       => 'O :attribute deve estar entre: min e :max digitos.',
    'dimensions'           => 'O :attribute tem dimensões de imagem inválidas.',
    'distinct'             => 'O campo do :attribute tem um valor duplicado.',
    'email'                => 'O :attribute deve ser um endereço de email válido.',
    'exists'               => 'O :attribute selecionado é inválido.',
    'file'                 => 'O :attribute deve ser um arquivo.',
    'filled'               => 'O campo :attribute deve ter um valor.',
    'image'                => 'O :attribute deve ser uma imagem.',
    'in'                   => 'O :attribute selecionado é inválido.',
    'in_array'             => 'O campo do :attribute não existe em :other.',
    'integer'              => 'O :attribute deve ser um número inteiro.',
    'ip'                   => 'O :attribute deve ser um endereço IP válido.',
    'ipv4'                 => 'O :attribute deve ser um endereço IPv4 válido.',
    'ipv6'                 => 'O :attribute deve ser um endereço IPv6 válido.',
    'json'                 => 'O :attribute deve ser uma sequência JSON válida.',
    'max'                  => [
        'numeric' => 'O :attribute não pode ser maior que :max.',
        'file'    => 'O :attribute não pode ser maior que :max kilobytes.',
        'string'  => 'O :attribute não pode ser maior que :max caracteres.',
        'array'   => 'O :attribute pode não ter mais que :max itens.',
    ],
    'mimes'                => 'O :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes'            => 'O :attribute deve ser um arquivo do tipo: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min itens.',
    ],
    'not_in'               => 'O :attribute selecionado: é inválido.',
    'numeric'              => 'O :attribute deve ser um número.',
    'present'              => 'O campo :attribute deve estar presente.',
    'regex'                => 'O formato do :attribute é inválido.',
    'required'             => 'O campo :attribute é obrigatório.',
    'required_if'          => 'O campo :attribute é obrigatório quando :other é :value.',
    'required_unless'      => 'O campo :attribute é obrigatório, a menos que :other esteja em :values.',
    'required_with'        => 'O campo :attribute é obrigatório quando :values estiver presente.',
    'required_with_all'    => 'O campo :attribute é obrigatório quando :values estiver presente.',
    'required_without'     => 'O campo :attribute é obrigatório quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum dos :valores estiver presente.',
    'same'                 => 'O :attribute e :other devem corresponder.',
    'size'                 => [
        'numeric' => 'O :attribute deve ser :size.',
        'file'    => 'O :attribute deve ser :size kilobytes.',
        'string'  => 'O :attribute deve ser :size de caracteres.',
        'array'   => 'O :attribute deve conter :size itens.',
    ],
    'string'               => 'O :attribute deve ser uma sequência.',
    'timezone'             => 'O :attribute deve ser uma zona válida.',
    'unique'               => 'O :attribute já foi utilizado.',
    'uploaded'             => 'O :attribute falhou ao carregar.',
    'url'                  => 'O formato do :attribute é inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
