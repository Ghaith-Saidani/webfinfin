<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;

use Serializable;



/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass=App\Repository\UserRepository::class)
 * 
 * 
 */
#[ORM\Entity(repositoryClass:UserRepository::class)]
#[UniqueEntity(fields:"email", message:"Email already exists" )]

class User implements UserInterface, Serializable
{
   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $iduser;

    #[ORM\Column(name:"first_name", type:"string", length:20, nullable:false)]
    #[Assert\NotBlank(message:"field is empty")]
    private ?string $firstName ;

    
    #[ORM\Column(name:"last_name", type:"string", length:20, nullable:false)]
    #[Assert\NotBlank(message:"field is empty")]
    private ?string $lastName ;

    
    #[ORM\Column(name:"email", type:"string", length:30, nullable:false)]
    #[Assert\NotBlank(message:"field is empty")]
    #[Assert\Email(message:"wrong form")]
    private ?string $email;

    #[ORM\Column(name:"roles", type:"json", length:10, nullable:false)]
   
    private array $roles = ['ROLE_USER'];

   
    #[ORM\Column(name:"incomeType", type:"string", length:10, nullable:false)]
    private ?string $incometype = 'Null';

    
    #[ORM\Column(name:"budgetType", type:"string", length:10, nullable:false)]
    private ?string $budgettype = 'Null';

    #[ORM\Column(name:"rent", type:"boolean",  nullable:true)]
    private ?bool $rent;

   
    #[ORM\Column(name:"debt", type:"boolean", nullable:true)]
    private ?bool $debt;

    
    #[ORM\Column(name:"transport", type:"string", length:10, nullable:false)]
    private ?string $transport = 'Null';

    
    #[ORM\Column(length:255, nullable:true)]
    private ?string $urlimage = null;

    /**
 * 
 * @var File
 */
    private $imageFile;

   
    #[ORM\Column(length:255)]
   
    private ?string $resetcode = null;

    #[ORM\Column(name:"password", type:"string", nullable:true)]
    #[Assert\NotBlank(message:"field is empty")]
    #[Assert\Length(min:8,minMessage:"password should be longer than 8 characters")]

    private ?string $password ='';

    #[Assert\EqualTo(propertyPath:"password", message:"password is not identical")]
    private $confirm_password;
    
      
    //#[ORM\Column(name:"googleAuthenticatorSecret", type:"string", nullable:true)]
    private $googleAuthenticatorSecret;
    
    

    public function getIduser(): ?int
    {
        return $this->iduser;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles=$this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function getIncometype(): ?string
    {
        return $this->incometype;
    }

    public function setIncometype(string $incometype)
    {
        $this->incometype = $incometype;

        return $this;
    }

    public function getBudgettype(): ?string
    {
        return $this->budgettype;
    }

    public function setBudgettype(string $budgettype)
    {
        $this->budgettype = $budgettype;

        return $this;
    }

    public function isRent(): ?bool
    {
        return $this->rent;
    }

    public function setRent(?bool $rent)
    {
        $this->rent = $rent;

        return $this;
    }

    public function isDebt(): ?bool
    {
        return $this->debt;
    }

    public function setDebt(?bool $debt)
    {
        $this->debt = $debt;

        return $this;
    }

    public function getTransport(): ?string
    {
        return $this->transport;
    }

    public function setTransport(string $transport)
    {
        $this->transport = $transport;

        return $this;
    }

  

    public function getUrlimage(): ?string
    {
        return $this->urlimage;
    }

    public function setUrlimage(?string $urlimage)
    {
        $this->urlimage = $urlimage;

        return $this;
    }
     /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     */
    public function setImageFile(?File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getResetcode(): ?string
    {
        return $this->resetcode;
    }

    public function setResetcode(?string $resetcode)
    {
        $this->resetcode = $resetcode;

        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }
    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password)
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }
    

    public function eraseCredentials()
    {
        // Implement any logic to erase sensitive data from the user object
        // For example, if you store plain-text passwords, you can clear them here
    }

    public function getUsername()
    {
        // Return the username for the user
        return $this->email; // Assuming email is the username
    }
    public function getSalt() {
        
    }
    public function serialize()
    {
        $this->imageFile = base64_encode($this->imageFile);
    }

    public function unserialize($serialized)
    {
        $this->imageFile = base64_decode($this->imageFile);

    }
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->googleAuthenticatorSecret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->email;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }
    public function isEmailAuthEnabled(): bool
    {
        return true; // This can be a persisted field to switch email code authentication on/off
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    

    
    function generateResetCode() {
        // Generate a random 6-digit code
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

}
