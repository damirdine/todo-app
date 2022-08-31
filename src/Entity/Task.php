<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'task')]
#[ORM\Index(name: 'category_id', columns: ['category_id'])]
#[ORM\Entity]
class Task
{
    #[ORM\Column(name: 'id_task', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $idTask;
   
    #[ORM\Column(name: 'name_task', type: 'string', length: 75, nullable: false)]
    private $nameTask;
    
    #[ORM\Column(name: 'description_task', type: 'text', length: 65535, nullable: false)]
    private $descriptionTask;
    
    #[ORM\Column(name: 'created_date_task', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $createdDateTask = 'CURRENT_TIMESTAMP';
    
    #[ORM\Column(name: 'due_date_task', type: 'datetime', nullable: false)]
    private $dueDateTask;
    
    #[ORM\Column(name: 'priority_task', type: 'string', length: 30, nullable: false)]
    private $priorityTask;
    
    #[ORM\ManyToOne(targetEntity: 'Categories')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id_category')]
    private $category;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?User $User = null;
    
    public function getIdTask(): ?int
    {
        return $this->idTask;
    }
    public function getNameTask(): ?string
    {
        return $this->nameTask;
    }
    public function setNameTask(string $nameTask): self
    {
        $this->nameTask = $nameTask;

        return $this;
    }
    public function getDescriptionTask(): ?string
    {
        return $this->descriptionTask;
    }
    public function setDescriptionTask(string $descriptionTask): self
    {
        $this->descriptionTask = $descriptionTask;

        return $this;
    }
    public function getCreatedDateTask(): ?\DateTimeInterface
    {
        return $this->createdDateTask;
    }
    public function setCreatedDateTask(\DateTimeInterface $createdDateTask): self
    {
        $this->createdDateTask = $createdDateTask;

        return $this;
    }
    public function getDueDateTask(): ?\DateTimeInterface
    {
        return $this->dueDateTask;
    }
    public function setDueDateTask(\DateTimeInterface $dueDateTask): self
    {
        $this->dueDateTask = $dueDateTask;

        return $this;
    }
    public function getPriorityTask(): ?string
    {
        return $this->priorityTask;
    }
    public function setPriorityTask(string $priorityTask): self
    {
        $this->priorityTask = $priorityTask;

        return $this;
    }
    public function getCategory(): ?Categories
    {
        return $this->category;
    }
    public function setCategory(?Categories $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

}
