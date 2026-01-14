<?php

namespace jam\app\utils;

use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\TemplateRole;

class DocuSign {
    static function sentNDAToEmail($signerEmail, $signerName) {
        $config = config('docusign');
        $tokenInfo = json_decode(file_get_contents(TOKENINFO_PATH));
        if (empty($tokenInfo->access_token)) {
            throw new \Exception('Empty access token');
        }
        return self::send([
            'envelope_args' => [
                'signer_email' => $signerEmail,
                'signer_name' => $signerName,
                'cc_email' => $config['cc_email'],
                'cc_name' => $config['cc_name'],
                'template_id' => $config['nda_template_id']
            ],
            'base_path' => $config['base_path'],
            'ds_access_token' => $tokenInfo->access_token,
            'account_id' => $config['account_id']
        ]);
    }

    static function send ($args) {
        $envelope_args = $args["envelope_args"];
        # Create the envelope request object
        $envelope_definition = static::make_envelope($envelope_args);
        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\Client\ApiClient($config);
        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        $envelope_id = $results->getEnvelopeId();
        return (object)['envelope_id' => $envelope_id];
    }

    /**
     * Creates envelope definition using a template
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    public static function make_envelope(array $args): EnvelopeDefinition {
        # create the envelope definition with the template_id
        $envelope_definition = new EnvelopeDefinition([
            'status' => 'sent', 'template_id' => $args['template_id']
        ]);
        # Create the template role elements to connect the signer and cc recipients
        # to the template
        $signer = new TemplateRole([
            'email' => $args['signer_email'],
            'name' => $args['signer_name'],
            'role_name' => 'signer'
        ]);

        # Add the TemplateRole objects to the envelope object
        $envelope_definition->setTemplateRoles([$signer /*, $cc*/]);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
