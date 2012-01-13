<?php
/**
 * 
 */
class Axiomes_Cache_DoctrineCompatible extends \Zend_Cache_Core implements \Doctrine\Common\Cache\Cache{

    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    function contains($id)
    {
        return (bool) $this->test( $this->_cleanCacheId($id) );
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    function delete($id)
    {
        return $this->remove( $this->_cleanCacheId($id) );
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    function fetch($id)
    {
        return $this->load( $this->_cleanCacheId($id) );
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache entry/data.
     * @param int $lifeTime The lifetime. If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    function save($id, $data, $lifeTime = 0)
    {
        return parent::save($data,  $this->_cleanCacheId($id) , array(), $lifeTime);
    }

    protected function _cleanCacheId($key){
        return preg_replace('/[^a-zA-Z0-9_]/','_', $key);
    }

	/**
	 * Retrieves cached information from data store
	 * The server's statistics array has the following values:
	 * - <b>hits</b>
	 * Number of keys that have been requested and found present.
	 * - <b>misses</b>
	 * Number of items that have been requested and not found.
	 * - <b>uptime</b>
	 * Time that the server is running.
	 * - <b>memory_usage</b>
	 * Memory used by this server to store items.
	 * - <b>memory_available</b>
	 * Memory allowed to use for storage.
	 *
	 * @since   2.2
	 * @return array|null  Associative array with server's statistics if available, NULL otherwise.
	 */
	function getStats(){
		return null;
	}
}
