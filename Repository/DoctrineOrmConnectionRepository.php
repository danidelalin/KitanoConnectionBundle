<?php

namespace Kitano\ConnectionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Kitano\ConnectionBundle\Model\ConnectionInterface;
use Kitano\ConnectionBundle\Proxy\DoctrineOrmConnection;
use Kitano\ConnectionBundle\Model\NodeInterface;

/**
 * ConnectionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DoctrineOrmConnectionRepository extends EntityRepository implements ConnectionRepositoryInterface
{
    /**
     * @var string
     */
    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $metadata = $em->getClassMetadata($class);
        parent::__construct($em, $metadata);

        $this->class = $class;
    }

    /**
     * @param NodeInterface $node
     * @param array         $filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getConnectionsWithSource(NodeInterface $node, array $filters = array())
    {
        $objectInformations = $this->extractMetadata($node);
        
        $objectClass = $objectInformations["object_class"];
        $objectId = $objectInformations["object_id"];
        
        $queryBuilder = $this->createQueryBuilder("connection");
        $queryBuilder->where("connection.sourceObjectClass = :objectClass");
        $queryBuilder->andWhere("connection.sourceObjectId = :objectId");
        $queryBuilder->setParameter("objectClass", $objectClass);
        $queryBuilder->setParameter("objectId", $objectId);
        
        $connections = $queryBuilder->getQuery()->getResult();
        
        foreach($connections as $connection) {
            $this->fillConnection($connection);
        }
        
        return $connections;
    }

    /**
     * @param \Kitano\ConnectionBundle\Model\NodeInterface $node
     * @param array $filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getConnectionsWithDestination(NodeInterface $node, array $filters = array())
    {
        $objectInformations = $this->extractMetadata($node);
        
        $objectClass = $objectInformations["object_class"];
        $objectId = $objectInformations["object_id"];
        
        $queryBuilder = $this->createQueryBuilder("connection");
        $queryBuilder->where("connection.destinationObjectClass = :objectClass");
        $queryBuilder->andWhere("connection.destinationObjectId = :objectId");
        $queryBuilder->setParameter("objectClass", $objectClass);
        $queryBuilder->setParameter("objectId", $objectId);
        
        $connections = $queryBuilder->getQuery()->getResult();
        
        foreach($connections as $connection) {
            $this->fillConnection($connection);
        }
        
        return $connections;
    }
    
    /**
     * @param ConnectionInterface $connection
     *
     * @return ConnectionInterface
     */
    public function update(ConnectionInterface $connection)
    {
        $sourceInformations = $this->extractMetadata($this->getSource());
        $destinationInformations = $this->extractMetadata($this->getDestination());
        
        $connection->setSourceObjectId($sourceInformations["object_id"]);
        $connection->setSourceObjectClass($sourceInformations["object_class"]);
        $connection->setDestinationObjectId($destinationInformations["object_id"]);
        $connection->setDestinationObjectClass($destinationInformations["object_class"]);
        
        $this->_em->persist($connection);
        $this->_em->flush();
        
        return $connection;
    }
    
    /**
     * @param ConnectionInterface $connection
     *
     * @return ConnectionRepositoryInterface
     */
    public function destroy(ConnectionInterface $connection)
    {
        $this->_em->remove($connection);
        $this->_em->flush();
        
        return $this;
    }
    
    /**
     * @return ConnectionInterface
     */
    public function createEmptyConnection()
    {
        return new $this->class();
    }
    
    /**
     * @param NodeInterface $node
     *
     * @return array
     */
    protected function extractMetadata(NodeInterface $node)
    {
        $classMetadata = $this->_em->getClassMetadata(get_class($node));
        
        return array(
            'object_class' => $classMetadata->getName(),
            'object_id' => $classMetadata->getIdentifierValues($node),
        );
    }
    
    /**
     * @param DoctrineOrmConnection $connection
     *
     * @return DoctrineOrmConnection
     */
    protected function fillConnection(DoctrineOrmConnection $connection)
    {
        $source = $this->_em->getRepository($connection->getSourceObjectClass())->find($connection->getSourceObjectId());
        $destination = $this->_em->getRepository($connection->getDestinationObjectClass())->find($connection->getDestinationObjectId());
        
        $connection->setSource($source);
        $connection->setDestination($destination);
        
        return $connection;
    }
}
