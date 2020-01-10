<?php
    namespace bmunyoki\Instagram\PersistentData;
    use Facebook\PersistentData\PersistentDataInterface;

    class LaravelPersistentDataHandler implements PersistentDataInterface{
        /**
         * {@inheritdoc}
         */
        public function get($key){
            return session()->get($key);
        }
        /**
         * {@inheritdoc}
         */
        public function set($key, $value){
            return session()->put($key, $value);
        }
    }