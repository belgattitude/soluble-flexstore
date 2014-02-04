<?php
namespace Soluble\FlexStore\Metadata\Column\Definition;


class StringColumn extends AbstractColumn
{
    /**
     *
     * @var int
     */
    protected $characterMaximumLength = null;



    /**
     * @return int|null the $characterMaximumLength
     */
    public function getCharacterMaximumLength()
    {
        return $this->characterMaximumLength;
    }

    /**
     * @param int $characterMaximumLength the $characterMaximumLength to set
     * @return \Soluble\FlexStore\Metadata\Column\StringColumn
     */
    public function setCharacterMaximumLength($characterMaximumLength)
    {
        $this->characterMaximumLength = $characterMaximumLength;
        return $this;
    }




}