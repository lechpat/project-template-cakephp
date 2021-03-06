<?php
namespace App\Model\Table;

use App\Avatar\Service as AvatarService;
use CakeDC\Users\Model\Table\UsersTable as Table;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Validation\Validator;
use CsvMigrations\Model\AssociationsAwareTrait;

/**
 * Users Model
 */
class UsersTable extends Table
{
    use AssociationsAwareTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Qobo/Utils.Footprint');

        $this->setAssociations();
    }

    /**
     * {@inheritDoc}
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->setColumnType('image', 'base64');

        return $schema;
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator = parent::validationDefault($validator);

        $validator->add('username', 'validRegex', [
            'rule' => ['custom', '/^[\w\d\@\-\_\s\.]+$/Du'],
            'message' => 'The provided value is invalid (alphanumeric, dot, dash, at, underscore, space)'
        ]);

        $validator->add('first_name', 'validRegex', [
            // \p is used for targeting unicode character properties, in this case L which means all letters
            // @link http://php.net/manual/en/regexp.reference.unicode.php
            'rule' => ['custom', '/^[\pL\-\s\.]+$/Du'],
            'message' => 'The provided value is invalid (letter, dot, dash, space)'
        ]);

        $validator->add('last_name', 'validRegex', [
            'rule' => ['custom', '/^[\pL\-\s\.]+$/Du'],
            'message' => 'The provided value is invalid (letter, dot, dash, space)'
        ]);

        return $validator;
    }

    /**
     * Custom finder method for adjusting the query when fetching authenticated user record.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance
     * @param mixed[] $options Query options
     * @return \Cake\Datasource\QueryInterface
     * @link https://book.cakephp.org/3.0/en/controllers/components/authentication.html#customizing-find-query
     */
    public function findAuth(QueryInterface $query, array $options) : QueryInterface
    {
        $query->where(['Users.active' => 1]);

        return $query;
    }

    /**
     * Checking if custom user avatar is present
     *
     * @param \Cake\Datasource\EntityInterface $entity of the user
     *
     * @return bool $result whether the file exists or not
     */
    public function isCustomAvatarExists(EntityInterface $entity): bool
    {
        $result = false;

        $avatarService = new AvatarService();
        $filename = $avatarService->getImageName(['id' => $entity->id]);

        $customDir = WWW_ROOT . Configure::read('Avatar.customDirectory');

        if (file_exists($customDir . $filename)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Copy custom user avatar to main avatar dir
     *
     * @param \Cake\Datasource\EntityInterface $entity of the user
     *
     * @return bool $result whether the copy of the files was successful
     */
    public function copyCustomAvatar(EntityInterface $entity): bool
    {
        $result = false;

        $avatarService = new AvatarService();
        $filename = $avatarService->getImageName(['id' => $entity->id]);

        $directory = WWW_ROOT . Configure::read('Avatar.directory');
        $customDir = WWW_ROOT . Configure::read('Avatar.customDirectory');

        $result = copy($customDir . $filename, $directory . $filename);

        return $result;
    }

    /**
     * Save Custom Avatar image for the User
     *
     * @param \Cake\Datasource\EntityInterface $entity of the user
     * @param resource $resource of the image file
     *
     * @return bool $result if file's saved.
     */
    public function saveCustomAvatar(EntityInterface $entity, $resource): bool
    {
        $result = false;

        $avatarService = new AvatarService();
        $filename = $avatarService->getImageName(['id' => $entity->id]);

        $customDir = WWW_ROOT . Configure::read('Avatar.customDirectory');

        $result = $avatarService->saveImage($customDir . $filename, $resource);

        if ($result) {
            $this->copyCustomAvatar($entity);
        }

        $avatarService->removeImageResource($resource);

        return $result;
    }
}
