<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Entity\Document;
use App\Utility\EncryptUtility;
use Exception;
use SodiumException;

/**
 * Documents Controller
 *
 * @property \App\Model\Table\DocumentsTable $Documents
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DocumentsController extends AppController
{
    /**
     * Handle our document uploads from the API request
     *
     * @return \Cake\Http\Response|null|void
     * @throws SodiumException
     * @throws Exception
     */
    public function handler()
    {
        // Run our housekeeping task to ensure no old files
        $this->Documents->houseCleanTmp();

        $user_id = $this->Auth->user('id');

        // Encrypt - Load our custom Utility
        $encryptUtility = new EncryptUtility();

        // Don't allow called direct
        if (!$this->request->is(['put', 'post'])) {
            die;
        }

        $attachment = $this->request->getData('file');
        $document = $this->Documents->newEmptyEntity();

        $filename = $attachment->getClientFilename();
        $size = $attachment->getSize();
        $error = $attachment->getError();

        // Work out our tmp path
        $path = $encryptUtility->getTmpPath();

        // Prefix
        $key = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        $filename = $key . '_' . $filename;

        // Does the file already exist?
        if ($this->Documents->checkFileExistsForUser($filename, $user_id) === true) {
            unset($attachment); // Ensure non-encrypted document is removed

            // Return our response
            return $this->response
                ->withStatus(403, 'Document already uploaded')
                ->withType('application/json')
                ->withStringBody(json_encode(['message' => 'This document is already uploaded.']));
        }

        // Move the file, prefixed with our username/hash to avoid conflicts
        $attachment->moveTo($path . DS . $filename);

        // @TODO Extend to check against all mime/extensions
        $mime = mime_content_type($path . DS . $filename);
        if (!empty($mime) && strpos($mime, 'application/x-dosexec') !== false) {
            // Delete the file
            unlink($path . DS . $filename);

            // Return our response
            return $this->response
                ->withStatus(403, 'There was a problem with the file')
                ->withType('application/json')
                ->withStringBody(json_encode(['message' => 'There was a problem with the file.']));
        }

        // Save to database
        // Load our 256 bit
        $key = $encryptUtility->readKey();

        $document = $this->Documents->patchEntity($document, [
            'owner' => $user_id,
            'filename' => $filename,
            'filesize' => $size,
            'secret_key' => $key,
        ]);

        // Encrypt
        $encrypted = $encryptUtility->encryptFile($filename, $filename, $key);

        // Destroy original/temp (now prefixed)
        $encryptUtility->destroyTmp($filename);

        // Did the encryption work?
        if (!$encrypted) {
            return $this->response
                ->withStatus(403, 'Encryption failed')
                ->withType('application/json')
                ->withStringBody(json_encode(['message' => 'Encryption failed']));
        }

        // Save
        $save = $this->Documents->save($document);

        if ($save) {
            unset($attachment); // Ensure non-encrypted document is removed

            // Return our response
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['result' => 'success', 'message' => 'File saved successfully', 'file' => $document]));
        } else {
            unset($attachment); // Ensure non-encrypted document is removed

            // Return our response
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['result' => 'fail', 'message' => 'File wasnt saved', 'errors' => $error]));
        }
    }

    /**
     * return documents that were uploaded by current user if user_id given
     * list of all documents if not
     *
     * @param int|null $user_id The model ID
     * @return \Cake\Http\Response|null|void Renders AJAX view
     */
    public function fetch(int $user_id = null)
    {
        // Use specific layout
        $this->viewBuilder()->setLayout('ajax');

        $conditions = [
            'owner' => $user_id,
        ];

        $documents = $this->Documents->find('all', [
            'conditions' => $user_id !== null ? ['owner' => $user_id] : [],
            'fields' => $user_id !== null ? ['id', 'filename', 'created'] : ['id', 'owner', 'filename', 'created'],
            'order' => ['created' => 'desc'],
        ])->toArray();

        // remove prefix
        foreach ($documents as $k => $d) {
            $documents[$k]->filename = $this->removePrefix($d->filename);
        }

        $this->set(compact('documents'));
        $this->viewBuilder()->setOption('serialize', ['documents']);
    }

    /**
     * Decrypt the document
     *
     * @param int $documentId The model ID
     * @return \Cake\Http\Response|null|void Renders AJAX view
     * @throws SodiumException
     */
    public function decrypt(int $documentId)
    {
        // Load our custom Utility
        $encryptUtility = new EncryptUtility();

        // Fetch file
        $document = $this->Documents->get($documentId, []);

        // Load our 256 bit
        $key = $document->secret_key;

        // Decrypt
        $decrypted = $encryptUtility->decryptFile($document->filename, $document->filename, $key);

        $decryptedDocumentPath = $encryptUtility->getTmpPath() . $document->filename;

        // Read file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($this->removePrefix($document->filename)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $document->filesize);
        ob_clean();
        flush();
        readfile($decryptedDocumentPath);

        // Destroy original/temp
        $encryptUtility->destroyTmp($document->filename);

        exit;
    }

    /**
     * Delete document from storage and database
     *
     * @param int $documentId Document id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(int $documentId)
    {
        $this->request->allowMethod(['post', 'delete']);
        $document = $this->Documents->get($documentId);

        // Delete the encrypted file
        // Load our custom Utility
        $encryptUtility = new EncryptUtility();
        $encryptUtility->destroyEncrypted($document->filename);

        // Delete database reference
        if ($this->Documents->delete($document)) {

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['result' => 'success', 'message' => 'File deleted']));
        } else {

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['result' => 'error', 'message' => 'Something went wrong, please try again']));
        }
    }
}
